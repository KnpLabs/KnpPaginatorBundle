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
        // use main symfony dispatcher
        if (!$container->hasDefinition('event_dispatcher') && !$container->hasAlias('event_dispatcher')) {
            return;
        }

        $definition = $container->findDefinition('event_dispatcher');

        foreach ($container->findTaggedServiceIds('knp_paginator.subscriber') as $id => $attributes) {
            // We must assume that the class value has been correcly filled, even if the service is created by a factory
            $class = $container->getDefinition($id)->getClass();

            $refClass = new \ReflectionClass($class);
            $interface = 'Symfony\Component\EventDispatcher\EventSubscriberInterface';
            if (!$refClass->implementsInterface($interface)) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interface));
            }

            foreach ($class::getSubscribedEvents() as $event => $options) {
                if (!is_array($options)) {
                    $options = array($options, 0);
                }
                $definition->addMethodCall('addListenerService', array(
                    $event,
                    array($id, $options[0]),
                    $options[1]
                ));
            }
            // sf 2.1.x only
            //$definition->addMethodCall('addSubscriberService', array($id, $class));
        }
    }
}
