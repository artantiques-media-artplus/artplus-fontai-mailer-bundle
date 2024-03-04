<?php
namespace Fontai\Bundle\MailerBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Fontai\Bundle\MailerBundle\DependencyInjection\Compiler\CompilerPass;


class MailerBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new CompilerPass());
  }
}