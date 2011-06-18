<?php

namespace Knplabs\Bundle\PaginatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('knplabs_paginator', 'array');
        $rootNode
            ->children()
                ->arrayNode('templating')
                    ->children()
                        ->scalarNode('style')->defaultValue('Sliding')->end()
                        ->scalarNode('template')->defaultValue('KnplabsPaginatorBundle:Pagination:sliding.html.twig')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
