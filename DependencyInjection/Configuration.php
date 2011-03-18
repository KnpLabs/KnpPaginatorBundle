<?php

namespace Knplabs\PaginatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTree()
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

        return $treeBuilder->buildTree();
    }
}
