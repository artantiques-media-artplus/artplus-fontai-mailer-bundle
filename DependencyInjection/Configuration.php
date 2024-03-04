<?php
namespace Fontai\Bundle\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;


class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder('mailer');

    $treeBuilder
    ->getRootNode()
      ->children()
        ->arrayNode('spool')
          ->children()
            ->arrayNode('propel')
              ->children()
                ->scalarNode('entity')->defaultValue('\App\Model\Email')->end()
                ->scalarNode('query')->defaultValue('\App\Model\EmailQuery')->end()
              ->end()
            ->end()
          ->end()
        ->end()
      ->end()
    ->end();

    return $treeBuilder;
  }
}