<?php

namespace Knplabs\PaginatorBundle\Tests\DependencyInjection;

use Knplabs\PaginatorBundle\Tests\BaseTestCase;
use Knplabs\PaginatorBundle\DependencyInjection\KnplabsPaginatorExtension;

class PaginatorExtensionTest extends BaseTestCase
{    
    protected function setUp()
    {
        $this->kernel = $this->getBaseKernelMock();
        $this->container = $this->getContainerBuilder();
    }
    
    public function testConfiguration()
    {
        $extension = new KnplabsPaginatorExtension();
        $extension->load(array(array()), $this->container);
        
        $this->assertFalse($this->container->has('templating.helper.knplabs_paginator'), 'Paginator helper should not be defined without templating support');
    }
    
    public function testSomeConfigurationOptions()
    {
        $extension = new KnplabsPaginatorExtension();
        $extension->load(array(array(
            'templating' => array('style' => 'Custom', 'template' => 'tpl.twig')
        )), $this->container);
        
        $this->assertTrue($this->container->hasParameter('knplabs_paginator.adapter.class'), 'Extension was not loaded properly, missing parameter');
        $this->assertTrue($this->container->has('templating.helper.knplabs_paginator'), 'Paginator helper was not built runtime');
        
        $def = $this->container->getDefinition('templating.helper.knplabs_paginator');
        $this->assertTrue($def->hasMethodCall('setTemplate'), 'Helper definition must have setTemplate method call');
        $calls = $def->getMethodCalls();
        foreach ($calls as $call) {
            if ($call[0] === 'setTemplate') {
                $this->assertEquals('tpl.twig', $call[1][0], 'Invalid parameter for setTemplate call');
                break;
            }
        }
    }
}