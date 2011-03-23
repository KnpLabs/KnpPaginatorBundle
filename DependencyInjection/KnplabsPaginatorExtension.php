<?php

namespace Knplabs\PaginatorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        $config = $processor->process($configuration->getConfigTree(), $configs);
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        if (isset($config['templating'])) {
            $loader->load('templating.xml');
            $helperDefinition = new Definition('%knplabs_paginator.templating.helper.class%');
            $helperDefinition
                ->addTag('templating.helper', array('alias' => 'pagination'))
                ->addMethodCall('setTemplate', array($config['templating']['template']))
                ->addMethodCall('setStyle', array($config['templating']['style']))
                ->setScope('request')
                ->setArguments(array(
                    new Reference('templating'),
                    new Reference('templating.helper.router'),
                    new Reference('request'),
                    new Reference('translator'),
                ));
            $container->setDefinition('templating.helper.knplabs_paginator', $helperDefinition);
            
            $twigExtensionDefinition = new Definition('%knplabs_paginator.twig.extension.class%');
            $twigExtensionDefinition
                ->addTag('twig.extension')
                ->setArguments(array(new Reference('service_container')));
            $container->setDefinition('knplabs_paginator.twig.extension', $twigExtensionDefinition);
        }
        $loader->load('paginator.xml');
    }
    
    /**
     * Populate the listener service ids
     * 
     * @param ContainerBuilder $container
     */
    public function populateListeners(ContainerBuilder $container)
    {
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
    
    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/symfony';
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getAlias()
    {
        return 'knplabs_paginator';
    }
}
