<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('knp_paginator')
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pagination')
                        ->defaultValue('KnpPaginatorBundle:Pagination:sliding.html.twig')
                        ->end()
                        ->scalarNode('sortable')
                        ->defaultValue('KnpPaginatorBundle:Pagination:sortable_link.html.twig')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('page_range')
                ->defaultValue(5)
                ->end()
            ->end()
        ;
        return $builder;
    }
}
