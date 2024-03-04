<?php
namespace Fontai\Bundle\MailerBundle\Model;

use App\Model;
use Fontai\Bundle\MailerBundle\Message\Swift_Message;


abstract class BaseEmailTemplate
{
  public function __construct()
  {
  }

  public function getEmailTemplateVariablesFromBody()
  {
    preg_match_all('~::[0-9A-Z_]+::~', $this->getBody(), $matches);

    return array_unique($matches[0]);
  }

  public function sendTest(
    string $to,
    array $variables = [],
    $culture = NULL
  )
  {
    global $kernel;

    if (!$kernel)
    {
      return FALSE;
    }

    $message = $this
    ->createEmail($variables)
    ->setTo($to);
    
    return $kernel
    ->getContainer()
    ->get('mailer')
    ->send($message);
  }

  public function createEmail(array $variables = [])
  {
    global $kernel;

    if (!$kernel)
    {
      return FALSE;
    }

    $container = $kernel->getContainer();

    $subject = strtr($this->getSubject(), $variables);
    
    $body = strtr($this->getBody(), $variables);
    preg_match_all('~::{[^}]+}::~', $body, $matches);

    if (isset($matches[0][0]))
    {
      $includes = [];

      foreach ($matches[0] as $match)
      {
        $includes[$match] = $container->get('translator')->trans(
          substr($match, 3, -3),
          [],
          'email',
          $this->getCulture()
        );
      }

      $body = strtr($body, $includes);
      $body = strtr($body, $variables);
    }

    $body = strtr($body, ['::SERVER::' => $container->getParameter('router.request_context.host')]);

    $body = $container->get('twig')->render('@Mailer/email.html.twig', [
      'body' => $body,
      'subject' => $subject
    ]);

    $message = (new Swift_Message($subject))
    ->setFrom([$this->getFrom() => $this->getFromName()])
    ->setPriority($this->getPriority());

    $body = $this->attachLocalImages(
      $message,
      $body,
      sprintf('%s/public', $container->getParameter('kernel.project_dir'))
    );
    
    $message->setBody($body, 'text/html');
    
    if ($this->getCc())
    {
      $message->setCc(array_map('trim', explode(',', $this->getCc())));
    }

    if ($this->getBcc())
    {
      $message->setBcc(array_map('trim', explode(',', $this->getBcc())));
    }

    if (!$this->getDynamicTo())
    {
      $message->setTo(array_map('trim', explode(',', $this->getTo())));
    }

    $emailTemplateAttachments = Model\EmailTemplateAttachmentQuery::create()
    ->joinWithI18n($this->getCulture())
    ->filterByEmailTemplate($this)
    ->find();

    foreach ($emailTemplateAttachments as $emailTemplateAttachment)
    {
      $file = $emailTemplateAttachment->getFileFile();

      if ($file)
      {
        $attachment = \Swift_Attachment::fromPath($file->getRealPath())
        ->setFilename($emailTemplateAttachment->getFilename())
        ->setContentType($emailTemplateAttachment->getMime());

        $message->attach($attachment);
      }
    }

    return $message;
  }

  protected function attachLocalImages(
    Swift_Message $message,
    string $body,
    string $publicPath
  )
  {
    preg_match_all('~src="(/[^"]+\.(?:jpe?g|png|gif))"~', $body, $matches);

    foreach ($matches[1] as $src)
    {
      $filePath = $publicPath . $src;

      if (is_file($filePath))
      {
        $cid = $message->embed(new \Swift_Image(
          file_get_contents($filePath),
          basename($filePath)
        ));
        $body = strtr($body, [$src => $cid]);
      }
    }

    return $body;
  }
}
