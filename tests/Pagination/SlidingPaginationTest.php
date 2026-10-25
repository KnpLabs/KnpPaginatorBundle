<?php

declare(strict_types=1);

namespace Knp\Bundle\PaginatorBundle\Tests\Pagination;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class SlidingPaginationTest.
 */
final class SlidingPaginationTest extends TestCase
{
    private SlidingPagination $pagination;

    protected function setUp(): void
    {
        $this->pagination = new SlidingPagination([]);
    }

    #[DataProvider('getPageLimitData')]
    public function testGetPageCount(int $expected, int $totalItemCount, int $itemsPerPage, ?int $pageLimit): void
    {
        $this->pagination->setTotalItemCount($totalItemCount);
        $this->pagination->setItemNumberPerPage($itemsPerPage);
        $this->pagination->setPageLimit($pageLimit);

        $this->assertSame($expected, $this->pagination->getPageCount());
    }

    #[DataProvider('getSortedData')]
    public function testSorted(bool $expected, string $sort, string $direction, $key): void
    {
        $this->pagination->setPaginatorOptions([
            'sortFieldParameterName' => 'sort',
            'sortDirectionParameterName' => 'direction',
        ]);
        $this->pagination->setParam('sort', $sort);
        $this->pagination->setParam('direction', $direction);

        $this->assertSame($expected, $this->pagination->isSorted($key));
    }

    #[DataProvider('getSortOverrideData')]
    public function testSortParameterOverrides(array $base, array $overrides, mixed $parameterName, array|string|null $key, bool $expected): void
    {
        $pagination = new SlidingPagination($base);
        $pagination->setPaginatorOptions(['sortFieldParameterName' => $parameterName]);

        self::assertSame($expected, $pagination->isSorted($key, $overrides));
    }

    public static function getSortOverrideData(): \Generator
    {
        yield 'override wins' => [['sort' => 'title'], ['sort' => 'other'], 'sort', 'other', true];
        yield 'null override clears sort' => [['sort' => 'title'], ['sort' => null], 'sort', null, false];
        yield 'null override does not match' => [['sort' => 'title'], ['sort' => null], 'sort', 'title', false];
        yield 'base used without override' => [['sort' => 'title'], ['filter' => 'value'], 'sort', 'title', true];
        yield 'missing sort' => [[], ['filter' => 'value'], 'sort', null, false];
        yield 'null base' => [['sort' => null], [], 'sort', null, false];
        yield 'empty sort exists' => [['sort' => ''], [], 'sort', null, true];
        yield 'strict comparison' => [['sort' => 1], [], 'sort', '1', false];
        yield 'compound sort' => [['sort' => 'title+subtitle'], [], 'sort', ['title', 'subtitle'], true];
        yield 'empty compound sort' => [['sort' => ''], [], 'sort', [], true];
        yield 'empty parameter name' => [['' => 'title'], [], '', 'title', true];
        yield 'numeric-looking string name' => [['01' => 'title'], [], '01', 'title', true];
        yield 'decimal string name' => [['1.2' => 'title'], [], '1.2', 'title', true];
        yield 'integer name retains its index' => [[7 => 'title'], [], 7, 'title', true];
        yield 'integer override wins' => [[7 => 'title'], [7 => 'other'], 7, 'other', true];
        yield 'integer null override clears sort' => [[7 => 'title'], [7 => null], 7, null, false];
        yield 'numeric string name retains its index' => [[7 => 'title'], [], '7', 'title', true];
        yield 'unrelated numeric override' => [[7 => 'title'], [9 => 'other'], 7, 'title', true];
        yield 'numeric index is not reindexed' => [[7 => 'title'], [], 0, 'title', false];
        yield 'numeric values do not override string sort' => [['sort' => 'title', 7 => 'other'], [9 => 'different'], 'sort', 'title', true];
    }

    #[DataProvider('getRangeBoundaryData')]
    public function testRangeBoundaries(int $total, int $range, int $current, array $expectedPages, int|float $expectedFirst, int|float $expectedLast): void
    {
        $this->pagination->setTotalItemCount($total);
        $this->pagination->setItemNumberPerPage(1);
        $this->pagination->setPageRange($range);
        $this->pagination->setCurrentPageNumber($current);

        // Repeated calls preserve the range after page range/current page normalization.
        for ($i = 0; $i < 2; ++$i) {
            $data = $this->pagination->getPaginationData();

            self::assertSame($expectedPages, $data['pagesInRange']);
            self::assertSame($expectedFirst, $data['firstPageInRange']);
            self::assertSame($expectedLast, $data['lastPageInRange']);
        }
    }

    public static function getRangeBoundaryData(): \Generator
    {
        yield 'empty pagination' => [0, 5, 1, [1.0, 0.0], 0.0, 1.0];
        yield 'single page' => [1, 5, 1, [1.0], 1.0, 1.0];
        yield 'beginning' => [100, 5, 1, [1, 2, 3, 4, 5], 1, 5];
        yield 'middle' => [100, 5, 50, [48.0, 49.0, 50.0, 51.0, 52.0], 48.0, 52.0];
        yield 'end' => [100, 5, 100, [96, 97, 98, 99, 100], 96, 100];
        yield 'even range' => [100, 4, 50, [49.0, 50.0, 51.0, 52.0], 49.0, 52.0];
        yield 'range above page count' => [3, 10, 2, [1.0, 2.0, 3.0], 1.0, 3.0];
        yield 'current above page count' => [10, 5, 100, [6, 7, 8, 9, 10], 6, 10];
        yield 'large range' => [20000, 10000, 15000, \range(10001.0, 20000.0), 10001.0, 20000.0];
        yield 'zero range descends' => [100, 0, 50, [51.0, 50.0], 50.0, 51.0];
        yield 'zero range at beginning' => [100, 0, 1, [2.0, 1.0], 1.0, 2.0];
        yield 'zero total and range' => [0, 0, 1, [1.0, 0.0], 0.0, 1.0];
        yield 'negative current' => [100, 5, -10, [1, 2, 3, 4, 5], 1, 5];
        yield 'negative range normalized' => [100, -5, 50, [48.0, 49.0, 50.0, 51.0, 52.0], 48.0, 52.0];
    }

    public static function getPageLimitData(): array
    {
        return [
            'Normal' => [5, 120, 25, null],
            'No pages' => [0, 0, 25, null],
            'Pages limited to 3' => [3, 400, 25, 3],
            'Pages limited to 3, but limit not hit' => [2, 26, 25, 3],
        ];
    }

    public static function getSortedData(): \Generator
    {
        yield [true, 'title', 'asc', null];
        yield [true, 'title', 'asc', 'title'];
        yield [true, 'title+subtitle', 'asc', 'title+subtitle'];
        yield [true, 'title+subtitle', 'asc', ['title', 'subtitle']];
        yield [false, 'title', 'asc', 'subtitle'];
    }
}
