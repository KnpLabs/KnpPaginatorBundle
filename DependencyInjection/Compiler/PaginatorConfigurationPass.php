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
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('knplabs_paginator.adapter')) {
            return;
        }

        // populate listener services
        $definition = $container->getDefinition('knplabs_paginator.adapter');

        foreach ($container->findTaggedServiceIds('knplabs_paginator.listener.orm') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $definition->addMethodCall('addListenerService', array($id, 'orm', $priority));
        }

        foreach ($container->findTaggedServiceIds('knplabs_paginator.listener.odm') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $definition->addMethodCall('addListenerService', array($id, 'odm', $priority));
        }
    }
}
