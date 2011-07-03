<?php

namespace Knp\Bundle\PaginatorBundle\Tests\Paginator;

use Knp\Bundle\PaginatorBundle\Tests\BaseTestCase;
use Knp\Bundle\PaginatorBundle\DependencyInjection\KnpPaginatorExtension;
use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorConfigurationPass;

class AdapterTest extends BaseTestCase
{
    protected function setUp()
    {
        $this->kernel = $this->getBaseKernelMock();
        $this->container = $this->getContainerBuilder();
    }

    public function testAdapter()
    {
        $extension = new KnpPaginatorExtension();
        $this->container->registerExtension($extension);
        $extension->load(array(array()), $this->container);

        $adapterDefinition = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $adapterDefinition->expects($this->exactly(4))
            ->method('addMethodCall')
            ->with('addListenerService');

        $this->container->setDefinition('knp_paginator.adapter', $adapterDefinition);
        $pass = new PaginatorConfigurationPass();
        $pass->process($this->container);
    }
}
