<?php

namespace Knplabs\Bundle\PaginatorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

class KnplabsPaginatorExtension extends Extension
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
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (isset($config['templating'])) {
            $loader->load('templating.xml');
            $helperDefinition = $container->getDefinition('templating.helper.knplabs_paginator');
            $helperDefinition
                ->addMethodCall('setTemplate', array($config['templating']['template']))
                ->addMethodCall('setStyle', array($config['templating']['style']));
        }
        $loader->load('paginator.xml');
    }
}
