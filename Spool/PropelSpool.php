<?php
namespace Fontai\Bundle\MailerBundle\Spool;

use Fontai\Bundle\MailerBundle\Message\Swift_Message;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;
use Symfony\Component\HttpFoundation\RequestStack;


class PropelSpool extends \Swift_ConfigurableSpool
{
  protected $entity;
  protected $query;
  protected $environment;
  protected $requestStack;

  public function __construct(
    string $entity,
    string $query,
    string $environment,
    RequestStack $requestStack
  )
  {
    $this->entity = $entity;
    $this->query = $query;
    $this->environment = $environment;
    $this->requestStack = $requestStack;
  }

  public function start()
  {
  }

  public function stop()
  {
  }

  public function isStarted()
  {
    return TRUE;
  }

  public function queueMessage(\Swift_Mime_SimpleMessage $message)
  {
    $email = new $this->entity();
    $email
    ->setEnvironment($this->environment)
    ->setMessage($message)
    ->setSenderEmail(current(array_keys($message->getFrom())))
    ->setSenderName(current(array_values($message->getFrom())))
    ->setSubject($message->getSubject())
    ->setBody($message->getBody());

    if ($message->getTo() !== NULL)
    {
      $email->setTo(array_keys($message->getTo()));
    }

    if ($message->getCc() !== NULL)
    {
      $email->setCc(array_keys($message->getCc()));
    }

    if ($message->getBcc() !== NULL)
    {
      $email->setBcc(array_keys($message->getBcc()));
    }

    $attachments = [];

    foreach ($message->getChildren() as $child)
    {
      if (!$child instanceof \Swift_Attachment)
      {
        continue;
      }

      $attachments[] = [
        $child->getFilename(),
        $child->getContentType()
      ];
    }

    $email->setAttachments($attachments);

    if ($message instanceof Swift_Message)
    {
      $email->setPriority($message->getPriority());
    }

    if ($email->save())
    {
      $request = $this->requestStack->getCurrentRequest();
      
      if ($request)
      {
        $request->attributes->set('mailer.spool.propel.queued', TRUE);
      }

      return TRUE;
    }
  }

  public function flushQueue(\Swift_Transport $transport, &$failedRecipients = NULL)
  {
    if (!$transport->isStarted())
    {
      $transport->start();
    }

    $limit = $this->getMessageLimit();
    
    $query = $this->query::create()
    ->filterByEnvironment($this->environment)
    ->filterBySendingAt(NULL)
    ->_or()
    ->filterBySendingAt(['max' => '-10 minute'])
    ->orderByPriority(Criteria::DESC)
    ->orderById()
    ->limit($limit > 0 ? $limit : 20)
    ->lockForUpdate();

    $con = Propel::getServiceContainer()->getWriteConnection($query->getDbName());

    return $con->transaction(function() use ($con, $query, $transport, $failedRecipients)
    {
      $emails = $query->find($con);
      
      if ($emails->isEmpty())
      {
        return 0;
      }

      $failedRecipients = (array) $failedRecipients;
      $count = 0;
      $startTime = time();

      foreach ($emails as $email)
      {
        $email
        ->setSendingAt('now')
        ->save($con);

        $message = $email->getMessage();
        
        try
        {
          $currentCount = $transport->send($message, $failedRecipients);
        }
        catch (\Exception $e)
        {
          break;
        }

        if ($currentCount > 0)
        {
          $count += $currentCount;
          $email->delete($con);

          if (
            ($message instanceof Swift_Message)
            && ($callback = $message->getCallback())
          )
          {
            $callback->call();
          }
        }
        
        if ($this->getTimeLimit() && (time() - $startTime) >= $this->getTimeLimit())
        {
          break;
        }
      }

      return $count;
    });
  }
}