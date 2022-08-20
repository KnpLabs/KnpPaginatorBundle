<?php

namespace Knp\Bundle\PaginatorBundle\Tests\DependencyInjection;

use Knp\Bundle\PaginatorBundle\DependencyInjection\Configuration;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class PaginatorAwarePassTest.
 */
final class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Processor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();
        $config = $this->processor->processConfiguration($this->configuration, []);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals([
            'default_options' => [
                'sort_field_name' => 'sort',
                'sort_direction_name' => 'direction',
                'filter_field_name' => 'filterField',
                'filter_value_name' => 'filterValue',
                'page_name' => 'page',
                'distinct' => true,
                'page_out_of_range' => PaginatorInterface::PAGE_OUT_OF_RANGE_IGNORE,
                'default_limit' => PaginatorInterface::DEFAULT_LIMIT_VALUE,
            ],
            'template' => [
                'pagination' => '@KnpPaginator/Pagination/sliding.html.twig',
                'filtration' => '@KnpPaginator/Pagination/filtration.html.twig',
                'sortable' => '@KnpPaginator/Pagination/sortable_link.html.twig',
            ],
            'page_range' => 5,
            'page_limit' => null,
            'convert_exception' => false,
        ], $config);
    }

    public function testCustomConfig(): void
    {
        $treeBuilder = $this->configuration->getConfigTreeBuilder();

        $expected = [
            'default_options' => [
                'sort_field_name' => 'yup',
                'sort_direction_name' => 'sure',
                'filter_field_name' => 'hi',
                'filter_value_name' => 'there',
                'page_name' => 'bar',
                'distinct' => false,
                'page_out_of_range' => PaginatorInterface::PAGE_OUT_OF_RANGE_FIX,
                'default_limit' => 20,
            ],
            'template' => [
                'pagination' => '@KnpPaginator/Pagination/foo.html.twig',
                'filtration' => '@KnpPaginator/Pagination/bar.html.twig',
                'sortable' => '@KnpPaginator/Pagination/baz.html.twig',
            ],
            'page_range' => 15,
            'page_limit' => 100,
            'convert_exception' => true,
        ];
        $config = $this->processor->processConfiguration($this->configuration, ['knp_paginator' => $expected]);

        $this->assertInstanceOf(ConfigurationInterface::class, $this->configuration);
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
        $this->assertEquals($expected, $config);
    }
}
