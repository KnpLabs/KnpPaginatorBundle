<?php

namespace Knp\Bundle\PaginatorBundle\Tests\DependencyInjection;

use Knp\Bundle\PaginatorBundle\DependencyInjection\KnpPaginatorExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class KnpPaginatorExtensionTest extends TestCase
{
    private const WRAP_QUERIES_OPTION = 'wrap-queries';

    /**
     * @return array<string, mixed>
     */
    private function loadDefaultOptions(array $config): array
    {
        $container = new ContainerBuilder();
        (new KnpPaginatorExtension())->load(['knp_paginator' => $config], $container);

        foreach ($container->getDefinition('knp_paginator')->getMethodCalls() as [$method, $arguments]) {
            if ('setDefaultPaginatorOptions' === $method) {
                return $arguments[0];
            }
        }

        self::fail('setDefaultPaginatorOptions was not configured on the knp_paginator service.');
    }

    public function testWrapQueriesDisabledByDefault(): void
    {
        $options = $this->loadDefaultOptions([]);

        self::assertArrayHasKey(self::WRAP_QUERIES_OPTION, $options, 'wrap-queries default option is missing');
        self::assertFalse($options[self::WRAP_QUERIES_OPTION]);
    }

    public function testWrapQueriesCanBeEnabled(): void
    {
        $options = $this->loadDefaultOptions(['default_options' => ['wrap_queries' => true]]);

        self::assertArrayHasKey(self::WRAP_QUERIES_OPTION, $options, 'wrap-queries default option is missing');
        self::assertTrue($options[self::WRAP_QUERIES_OPTION]);
    }
}
