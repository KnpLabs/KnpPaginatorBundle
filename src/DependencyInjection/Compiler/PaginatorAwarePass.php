<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PaginatorAwarePass.
 *
 * This compiler scans for the 'knp_paginator.injectable' tag and injects the Paginator service.
 */
final class PaginatorAwarePass implements CompilerPassInterface
{
    public const PAGINATOR_AWARE_TAG = 'knp_paginator.injectable';

    public const PAGINATOR_AWARE_INTERFACE = PaginatorAwareInterface::class;

    /**
     * Populates all tagged services with the paginator service.
     *
     * @throws \InvalidArgumentException
     * @throws InvalidDefinitionException
     */
    public function process(ContainerBuilder $container): void
    {
        $defaultAttributes = ['paginator' => 'knp_paginator'];

        foreach ($container->findTaggedServiceIds(self::PAGINATOR_AWARE_TAG) as $id => [$attributes]) {
            $definition = $container->getDefinition($id);
            if (null === $class = $definition->getClass()) {
                throw new \InvalidArgumentException(\sprintf('Service "%s" not found.', $id));
            }
            /** @var class-string $class */
            $refClass = new \ReflectionClass($class);
            if (!$refClass->implementsInterface(self::PAGINATOR_AWARE_INTERFACE)) {
                throw new \InvalidArgumentException(\sprintf('Service "%s" must implement interface "%s".', $id, self::PAGINATOR_AWARE_INTERFACE));
            }

            $attributes = \array_merge($defaultAttributes, $attributes);
            if (!$container->has($attributes['paginator'])) {
                throw new InvalidDefinitionException(\sprintf('Paginator service "%s" for tag "%s" on service "%s" could not be found.', $attributes['paginator'], self::PAGINATOR_AWARE_TAG, $id));
            }

            $definition->addMethodCall('setPaginator', [new Reference($attributes['paginator'])]);
            $container->setDefinition($id, $definition);
        }
    }
}
