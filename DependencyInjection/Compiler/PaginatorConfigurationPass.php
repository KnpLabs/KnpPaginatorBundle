<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler;

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
        if (!$container->hasDefinition('knp_paginator.adapter')) {
            return;
        }

        // populate listener services
        $definition = $container->getDefinition('knp_paginator.adapter');

        foreach ($container->findTaggedServiceIds('knp_paginator.listener.orm') as $id => $attributes) {
            $definition->addMethodCall('addListenerService', array($id, 'orm'));
        }

        foreach ($container->findTaggedServiceIds('knp_paginator.listener.odm') as $id => $attributes) {
            $definition->addMethodCall('addListenerService', array($id, 'odm'));
        }

    }
}
