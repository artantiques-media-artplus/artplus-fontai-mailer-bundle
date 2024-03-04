<?php
namespace Fontai\Bundle\MailerBundle\Model;

use App\Model\EmailLog;
use Propel\Runtime\Connection\ConnectionInterface;


abstract class BaseEmail
{
  public function __construct()
  {
  }
  
  public function addTo($email)
  {
    $emails = $this->getTo();
    $emails[] = $email;

    return $this->setTo($emails);
  }

  public function addCc($email)
  {
    $emails = $this->getCc();
    $emails[] = $email;

    return $this->setCc($emails);
  }

  public function addBcc($email)
  {
    $emails = $this->getBcc();
    $emails[] = $email;

    return $this->setBcc($emails);
  }

  public function getHumanSender()
  {
    $name = trim($this->getSenderName());
    
    if ($name && ($name == $this->getSenderEmail()))
    {
      $name = NULL;
    }

    return ($name ? $name . ' <' : NULL) . $this->getSenderEmail() . ($name ? '>' : NULL);
  }
  
  public function setConfimObject($object)
  {
    $this->setConfirmParam(sprintf(
      '%s;%s',
      get_class($object),
      $object->getId()
    ));

    return $this;
  }

  public function preDelete(ConnectionInterface $con = NULL)
  {
    $this->moveToEmailLog();

    return TRUE;
  }

  protected function moveToEmailLog()
  {
    $emailLog = new EmailLog();
    
    return $emailLog
    ->setSenderName($this->getSenderName())
    ->setSenderEmail($this->getSenderEmail())
    ->setTo($this->getTo())
    ->setCc($this->getCc())
    ->setBcc($this->getBcc())
    ->setSubject($this->getSubject())
    ->setAttachments($this->getAttachments())
    ->save();
  }
}
