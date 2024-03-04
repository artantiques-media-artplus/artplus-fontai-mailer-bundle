<?php
namespace Fontai\Bundle\MailerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class MailerExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('mailer.yaml');

    $container->setParameter('mailer.spool.propel.entity', $config['spool']['propel']['entity']);
    $container->setParameter('mailer.spool.propel.query',  $config['spool']['propel']['query']);
  }

  public function getAlias()
  {
    return 'mailer';
  }
}