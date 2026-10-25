<?php

declare(strict_types=1);

namespace Knp\Bundle\PaginatorBundle\Tests\Helper;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ProcessorTest extends TestCase
{
    #[DataProvider('getSortableData')]
    public function testSortable(array $overrides, array $options, bool $sorted, string $direction, string $class): void
    {
        $pagination = new SlidingPagination(['sort' => 'title+subtitle', 'direction' => 'asc', 'page' => 3, 'filter' => 'active']);
        $pagination->setUsedRoute('articles');
        $pagination->setPaginatorOptions([
            'sortFieldParameterName' => 'sort',
            'sortDirectionParameterName' => 'direction',
            'pageParameterName' => 'page',
        ]);

        $router = $this->createMock(UrlGeneratorInterface::class);
        $router->expects(self::once())->method('generate')->with('articles', [
            'sort' => 'title+subtitle',
            'direction' => $direction,
            'page' => 1,
            'filter' => 'active',
        ], UrlGeneratorInterface::ABSOLUTE_PATH)->willReturn('/articles');

        $processor = new Processor($router, $this->createStub(TranslatorInterface::class));
        $result = $processor->sortable($pagination, 'Title', ['title', 'subtitle'], $options, $overrides);

        self::assertSame($sorted, $result['sorted']);
        self::assertSame($direction, $result['direction']);
        self::assertSame($class, $result['options']['class']);
        self::assertSame('/articles', $result['options']['href']);
        self::assertSame(['sort' => 'title+subtitle', 'direction' => 'asc', 'page' => 3, 'filter' => 'active'], $pagination->getParams());
    }

    public static function getSortableData(): \Generator
    {
        yield 'toggle current sort' => [[], [], true, 'desc', 'asc'];
        yield 'fixed direction' => [['direction' => 'asc'], [], true, 'asc', 'desc'];
        yield 'different sort override' => [['sort' => 'other'], [], false, 'asc', 'sortable'];
        yield 'null sort override' => [['sort' => null], [], false, 'asc', 'sortable'];
        yield 'null direction uses default' => [['direction' => null], ['defaultDirection' => 'desc'], true, 'asc', 'desc'];
    }
}
