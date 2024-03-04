<?php
namespace Fontai\Bundle\MailerBundle\Message;

class Swift_Message extends \Swift_Message
{
  protected $callback;
  protected $priority = 0;

  public function setCallback(Callback $callback)
  {
    $this->callback = $callback;

    return $this;
  }

  public function setPriority($priority)
  {
    $this->priority = $priority;

    return $this;
  }

  public function getCallback()
  {
    return $this->callback;
  }

  public function getPriority()
  {
    return $this->priority;
  }
}