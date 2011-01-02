<?php

namespace Bundle\DoctrinePaginatorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrinePaginatorExtension extends Extension
{
    /**
     * Loads the AssetPackager configuration.
     *
     * @param array $config An array of configuration settings
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        //die(print_r($config));
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('paginator.xml');
        $this->applyUserConfig($config, $container, 'doctrine_paginator');
        //die(print_r($container->getParameterBag()));
    }
    
    /**
     * Handles the menu.templating configuration.
     *
     * @param array $config The configuration being loaded
     * @param ContainerBuilder $container
     */
    public function templatingLoad(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
        $loader->load('templating.xml');
        $this->applyUserConfig($config, $container, 'doctrine_paginator.templating');
    }
    
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
        return 'doctrine_paginator';
    }
}
