<?php

namespace Knplabs\Bundle\PaginatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class PaginatorConfigurationPass implements CompilerPassInterface
{
    /**
     * Populate the listener service ids
     *
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->getExtension('knplabs_paginator')->populateListeners($container);
    }

}
