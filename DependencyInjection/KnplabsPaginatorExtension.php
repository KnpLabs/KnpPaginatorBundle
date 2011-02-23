<?php

namespace Knplabs\PaginatorBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KnplabsPaginatorExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('paginator.xml');
        $loader->load('templating.xml');
        //die(print_r($container->getParameterBag()));
        //$this->applyUserConfig($config, $container, 'knplabs_paginator');
    }
    
    /**
     * Processes the user $config parameters into
     * $container, prefixed by $prefix recursivelly
     * 
     * @param array $config
     * @param ContainerBuilder $container
     * @param string $prefix
     * @return void
     */
    protected function applyUserConfig(array $config, ContainerBuilder $container, $prefix = '')
    {
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $this->applyUserConfig($value, $container, $prefix . '.' . $name);
            } else {
                $container->setParameter($prefix . '.' . $name, $value);
            }
        }
    }
    
    public function configValidate(ContainerBuilder $container)
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
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getAlias()
    {
        return 'knplabs_paginator';
    }
}
