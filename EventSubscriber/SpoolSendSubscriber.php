<?php
namespace Fontai\Bundle\MailerBundle\EventSubscriber;

use Fontai\Bundle\MailerBundle\Spool\PropelSpool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;


class SpoolSendSubscriber implements EventSubscriberInterface
{
  protected $container;
  protected $exceptionThrown = FALSE;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function onKernelException(ExceptionEvent $event)
  {
    $this->exceptionThrown = TRUE;
  }

  public function onKernelTerminate(TerminateEvent $event)
  {
    if ($this->exceptionThrown || !$event->getRequest()->attributes->get('mailer.spool.propel.queued'))
    {
      return;
    }

    $this->flushPropelSpool();
  }

  protected function flushPropelSpool()
  {
    $mailers = array_keys($this->container->getParameter('swiftmailer.mailers'));
        
    foreach ($mailers as $name)
    {
      if (method_exists($this->container, 'initialized') && !$this->container->initialized(sprintf('swiftmailer.mailer.%s', $name)))
      {
        continue;
      }
      
      if (!$this->container->getParameter(sprintf('swiftmailer.mailer.%s.spool.enabled', $name)))
      {
        continue;
      }
    
      $mailer = $this->container->get(sprintf('swiftmailer.mailer.%s', $name));
      $transport = $mailer->getTransport();

      if (!$transport instanceof \Swift_Transport_SpoolTransport)
      {
        continue;
      }
      
      $spool = $transport->getSpool();

      if (!$spool instanceof PropelSpool)
      {
        continue;
      }

      $spool->setMessageLimit(10);
      $spool->flushQueue($this->container->get(sprintf('swiftmailer.mailer.%s.transport.real', $name)));
    }
  }

  public static function getSubscribedEvents()
  {
    return [
      KernelEvents::TERMINATE => [
        ['onKernelTerminate', 0]
      ],
      KernelEvents::EXCEPTION => [
        ['onKernelException', 0]
      ]
    ];
  }
}