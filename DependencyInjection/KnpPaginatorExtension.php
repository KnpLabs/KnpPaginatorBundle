<?php

namespace Knp\Bundle\PaginatorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

class KnpPaginatorExtension extends Extension
{
    /**
     * Build the extension services
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('paginator.xml');

        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('knp_paginator.template.pagination', $config['template']['pagination']);
        $container->setParameter('knp_paginator.template.sortable', $config['template']['sortable']);
        $container->setParameter('knp_paginator.page_range', $config['page_range']);

        $paginatorDef = $container->getDefinition('knp_paginator');
        $paginatorDef->addMethodCall('setDefaultPaginatorOptions', array(array(
            'pageParameterName' => $config['default_options']['page_name'],
            'sortFieldParameterName' => $config['default_options']['sort_field_name'],
            'sortDirectionParameterName' => $config['default_options']['sort_direction_name'],
            'distinct' => $config['default_options']['distinct']
        )));
    }
}
