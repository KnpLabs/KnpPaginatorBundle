<?php

namespace Knplabs\Bundle\PaginatorBundle\Tests\Paginator;

use Knplabs\Bundle\PaginatorBundle\Tests\BaseTestCase;
use Knplabs\Bundle\PaginatorBundle\DependencyInjection\KnplabsPaginatorExtension;
use Knplabs\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorConfigurationPass;

class AdapterTest extends BaseTestCase
{
    protected function setUp()
    {
        $this->kernel = $this->getBaseKernelMock();
        $this->container = $this->getContainerBuilder();
    }

    public function testAdapter()
    {
        $extension = new KnplabsPaginatorExtension();
        $this->container->registerExtension($extension);
        $extension->load(array(array()), $this->container);

        $adapterDefinition = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $adapterDefinition->expects($this->exactly(4))
            ->method('addMethodCall')
            ->with('addListenerService');

        $this->container->setDefinition('knplabs_paginator.adapter', $adapterDefinition);
        $pass = new PaginatorConfigurationPass();
        $pass->process($this->container);
    }
}
