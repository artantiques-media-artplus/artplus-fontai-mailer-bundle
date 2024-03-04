<?php
namespace Fontai\Bundle\MailerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class CompilerPass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    $configs = $container->getExtensionConfig('swiftmailer');

    foreach ($configs as $config)
    {
      if (!isset($config['mailers']))
      {
        return;
      }

      foreach ($config['mailers'] as $name => $mailer)
      {
        if (!isset($mailer['spool']))
        {
          continue;
        }

        $type = $mailer['spool']['type'];

        if ($type != 'propel')
        {
          continue;
        }

        $container->setAlias(sprintf('swiftmailer.mailer.%s.spool.propel', $name), 'swiftmailer.spool.propel.abstract');
      }
    }
  }
}
