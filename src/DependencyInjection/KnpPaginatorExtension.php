<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection;

use Knp\Bundle\PaginatorBundle\EventListener\ExceptionListener;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class KnpPaginatorExtension extends Extension
{
    /**
     * Build the extension services.
     *
     * @param array<string, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('paginator.xml');

        if ($container->hasParameter('templating.engines')) {
            /** @var array<string> $engines */
            $engines = $container->getParameter('templating.engines');
            if (\in_array('php', $engines, true)) {
                $loader->load('templating_php.xml');
            }
        }

        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('knp_paginator.template.pagination', $config['template']['pagination']);
        $container->setParameter('knp_paginator.template.filtration', $config['template']['filtration']);
        $container->setParameter('knp_paginator.template.sortable', $config['template']['sortable']);
        $container->setParameter('knp_paginator.page_range', $config['page_range']);
        $container->setParameter('knp_paginator.page_limit', $config['page_limit']);

        $paginatorDef = $container->getDefinition('knp_paginator');
        $paginatorDef->addMethodCall('setDefaultPaginatorOptions', [[
            'pageParameterName' => $config['default_options']['page_name'],
            'sortFieldParameterName' => $config['default_options']['sort_field_name'],
            'sortDirectionParameterName' => $config['default_options']['sort_direction_name'],
            'filterFieldParameterName' => $config['default_options']['filter_field_name'],
            'filterValueParameterName' => $config['default_options']['filter_value_name'],
            'distinct' => $config['default_options']['distinct'],
            'pageOutOfRange' => $config['default_options']['page_out_of_range'],
            'defaultLimit' => $config['default_options']['default_limit'],
        ]]);

        if ($config['convert_exception']) {
            $definition = new Definition(ExceptionListener::class);
            $definition->addTag('kernel.event_listener', ['event' => 'kernel.exception']);
            $container->setDefinition(ExceptionListener::class, $definition);
        }
    }
}
