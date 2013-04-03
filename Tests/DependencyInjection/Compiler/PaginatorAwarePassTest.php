<?php

namespace Knp\Bundle\PaginatorBundle\Tests\DependencyInjection\Compiler;

use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorAwarePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PaginatorAwarePassTest
 */
class PaginatorAwarePassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $container;

    /**
     * @var PaginatorAwarePass
     */
    public $pass;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->pass      = new PaginatorAwarePass();
    }

    public function testCorrectPassProcess()
    {
        $tagged = array(
            'tag.one' => array('paginator' => 'knp.paginator')
        );

        $classes = array(
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Definition\PaginatorAware'
        );

        $definition = $this->setUpContainerMock('tag.one', $tagged, $classes);

        $tested = clone $definition;
        $tested->addMethodCall('setPaginator', array(new Reference('knp.paginator')));

        $this->container
            ->expects($this->once())
            ->method('setDefinition')
            ->with('tag.one', $tested)
        ;

        $this->pass->process($this->container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Service "tag.one" must implement interface "Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface".
     */
    public function testExceptionWrongInterface()
    {
        $tagged = array(
            'tag.one' => array('paginator' => 'knp.paginator')
        );

        $classes = array(
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Helper\Processor'
        );

        $this->setUpContainerMock('tag.one', $tagged, $classes, true, true);
        $this->pass->process($this->container);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidDefinitionException
     * @expectedExceptionMessage Paginator service "INVALID" for tag "knp_paginator.injectable" on service "tag.one" could not be found.
     */
    public function testExceptionNoPaginator()
    {
        $tagged = array(
            'tag.one' => array('paginator' => 'INVALID')
        );

        $classes = array(
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Definition\PaginatorAware'
        );

        $this->setUpContainerMock('tag.one', $tagged, $classes, false);
        $this->pass->process($this->container);
    }

    private function setUpContainerMock($id, $services, $classes, $return = true, $exception = false)
    {
        $definition = new Definition($classes[$id]);

        $this->container
            ->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with(PaginatorAwarePass::PAGINATOR_AWARE_TAG)
            ->will($this->returnValue($services))
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with($id)
            ->will($this->returnValue($definition))
        ;

        if (!$exception) {
            $this->container
                ->expects($this->once())
                ->method('has')
                ->with($services[$id]['paginator'])
                ->will($this->returnValue($return))
            ;
        }

        return $definition;
    }
}
