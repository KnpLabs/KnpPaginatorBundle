<?php

namespace Knplabs\PaginatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class PaginatorConfigurationPass implements CompilerPassInterface
{

    /**
     * Validate the DoctrineExtensions DIC extension config.
     *
     * This validation runs in a discrete compiler pass
     *
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $container->getExtension('knplabs_paginator')->configValidate($container);
    }

}