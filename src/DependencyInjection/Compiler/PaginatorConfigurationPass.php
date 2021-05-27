<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

final class PaginatorConfigurationPass implements CompilerPassInterface
{
    /**
     * Populate the listener service ids.
     */
    public function process(ContainerBuilder $container): void
    {
        // use main symfony dispatcher
        if (!$container->hasDefinition('event_dispatcher') && !$container->hasAlias('event_dispatcher')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('knp_paginator.listener') as $serviceId => $tags) {
            @\trigger_error('Using "knp_paginator.listener" tag is deprecated, use "kernel.event_listener" instead.', \E_USER_DEPRECATED);
        }

        foreach ($container->findTaggedServiceIds('knp_paginator.subscriber') as $serviceId => $tags) {
            @\trigger_error('Using "knp_paginator.subscriber" tag is deprecated, use "kernel.event_subscriber" instead.', \E_USER_DEPRECATED);
        }

        $pass = new RegisterListenersPass('event_dispatcher', 'knp_paginator.listener', 'knp_paginator.subscriber');
        $pass->process($container);
    }
}
