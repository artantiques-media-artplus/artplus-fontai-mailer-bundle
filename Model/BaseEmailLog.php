<?php
namespace Fontai\Bundle\MailerBundle\Model;

use App\Model\AdminQuery;


abstract class BaseEmailLog
{
  public function __construct()
  {
  }
  
  public function __toString()
  {
    return sprintf(
      '%s - %s',
      $this->getCreatedAt('j. n. Y H:i'),
      $this->getSubject()
    );
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

  public function getGroup()
  {
    $groups = [];
    $recipient = $this->getTo();
    
    foreach (AdminQuery::create()->select('Email')->find() as $email)
    {
      if (!($email = trim($email)))
      {
        continue;
      }

      if (in_array($email, $recipient))
      {
        $groups[] = 'Administrátoři';
      }
    }

    return $groups;
  }
}
