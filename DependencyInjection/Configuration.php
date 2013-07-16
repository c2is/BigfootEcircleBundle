<?php

namespace Bigfoot\Bundle\EcircleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bigfoot_ecircle');

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode('wsdl_url')->end()
                        ->arrayNode('request')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('realm')->isRequired()->end()
                                    ->scalarNode('user')->isRequired()->end()
                                    ->scalarNode('passwd')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
