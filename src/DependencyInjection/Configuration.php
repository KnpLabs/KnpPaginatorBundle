<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('knp_paginator');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('default_options')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('sort_field_name')->defaultValue('sort')->end()
                        ->scalarNode('sort_direction_name')->defaultValue('direction')->end()
                        ->scalarNode('filter_field_name')->defaultValue('filterField')->end()
                        ->scalarNode('filter_value_name')->defaultValue('filterValue')->end()
                        ->scalarNode('page_name')->defaultValue('page')->end()
                        ->booleanNode('distinct')->defaultTrue()->end()
                        ->scalarNode('page_out_of_range')->defaultValue(PaginatorInterface::PAGE_OUT_OF_RANGE_IGNORE)->end()
                        ->scalarNode('default_limit')->defaultValue(PaginatorInterface::DEFAULT_LIMIT_VALUE)->end()
                    ->end()
                ->end()
                ->arrayNode('template')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pagination')
                        ->defaultValue('@KnpPaginator/Pagination/sliding.html.twig')
                        ->end()
                        ->scalarNode('rel_links')
                        ->defaultValue('@KnpPaginator/Pagination/rel_links.html.twig')
                        ->end()
                        ->scalarNode('filtration')
                        ->defaultValue('@KnpPaginator/Pagination/filtration.html.twig')
                        ->end()
                        ->scalarNode('sortable')
                        ->defaultValue('@KnpPaginator/Pagination/sortable_link.html.twig')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('page_range')
                ->defaultValue(5)
                ->end()
                ->scalarNode('page_limit')
                ->defaultNull()
                ->validate()
                    ->ifTrue(static fn (mixed $v): bool => null !== $v && !\is_int($v))
                    ->thenInvalid('The page_limit must be an integer or null.')
                ->end()
                ->end()
                ->booleanNode('convert_exception')
                ->defaultFalse()
                ->end()
                ->booleanNode('remove_first_page_param')
                ->defaultFalse()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
