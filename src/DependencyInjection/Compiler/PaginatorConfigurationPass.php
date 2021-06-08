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

        $listeners = $container->findTaggedServiceIds('knp_paginator.listener');
        $subscribers = $container->findTaggedServiceIds('knp_paginator.subscriber');

        foreach ($listeners as $serviceId => $tags) {
            @\trigger_error('Using "knp_paginator.listener" tag is deprecated, use "kernel.event_listener" instead.', \E_USER_DEPRECATED);
        }

        foreach ($subscribers as $serviceId => $tags) {
            @\trigger_error('Using "knp_paginator.subscriber" tag is deprecated, use "kernel.event_subscriber" instead.', \E_USER_DEPRECATED);
        }

        if (\count($listeners) > 0 || \count($subscribers) > 0) {
            $pass = new RegisterListenersPass('event_dispatcher', 'knp_paginator.listener', 'knp_paginator.subscriber');
            $pass->process($container);
        }
    }
}
