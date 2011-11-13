<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('knp_paginator', 'array');
        $rootNode
            ->children()
                ->arrayNode('templating')
                    ->children()
                        ->scalarNode('pagination_template')->defaultValue('KnpPaginatorBundle:Pagination:sliding.html.twig')->end()
                        ->scalarNode('sort_link_template')->defaultValue('KnpPaginatorBundle:Pagination:sortable_link.html.twig')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
