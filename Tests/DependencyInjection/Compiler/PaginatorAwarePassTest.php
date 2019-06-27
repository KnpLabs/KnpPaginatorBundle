<?php

namespace Knp\Bundle\PaginatorBundle\Tests\DependencyInjection\Compiler;

use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorAwarePass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PaginatorAwarePassTest.
 */
final class PaginatorAwarePassTest extends TestCase
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
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->getMock();
        $this->pass = new PaginatorAwarePass();
    }

    public function testCorrectPassProcess(): void
    {
        $tagged = [
            'tag.one' => ['paginator' => 'knp.paginator'],
        ];

        $classes = [
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Definition\PaginatorAware',
        ];

        $definition = $this->setUpContainerMock('tag.one', $tagged, $classes);

        $tested = clone $definition;
        $tested->addMethodCall('setPaginator', [new Reference('knp.paginator')]);

        $this->container
            ->expects($this->once())
            ->method('setDefinition')
            ->with('tag.one', $tested)
        ;

        $this->pass->process($this->container);
    }

    public function testExceptionWrongInterface(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "tag.one" must implement interface "Knp\\Bundle\\PaginatorBundle\\Definition\\PaginatorAwareInterface".');

        $tagged = [
            'tag.one' => ['paginator' => 'knp.paginator'],
        ];

        $classes = [
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Helper\Processor',
        ];

        $this->setUpContainerMock('tag.one', $tagged, $classes, true, true);
        $this->pass->process($this->container);
    }

    public function testExceptionNoPaginator(): void
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException::class);
        $this->expectExceptionMessage('Paginator service "INVALID" for tag "knp_paginator.injectable" on service "tag.one" could not be found.');

        $tagged = [
            'tag.one' => ['paginator' => 'INVALID'],
        ];

        $classes = [
            'tag.one' => 'Knp\Bundle\PaginatorBundle\Definition\PaginatorAware',
        ];

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
            ->willReturn($services)
        ;

        $this->container
            ->expects($this->once())
            ->method('getDefinition')
            ->with($id)
            ->willReturn($definition)
        ;

        if (!$exception) {
            $this->container
                ->expects($this->once())
                ->method('has')
                ->with($services[$id]['paginator'])
                ->willReturn($return)
            ;
        }

        return $definition;
    }
}
