<?php

declare(strict_types=1);

namespace Knp\Bundle\PaginatorBundle\Tests\Pagination;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\TestCase;

/**
 * Class SlidingPaginationTest.
 */
final class SlidingPaginationTest extends TestCase
{
    /**
     * @var SlidingPagination
     */
    private $pagination;

    protected function setUp(): void
    {
        $this->pagination = new SlidingPagination([]);
    }

    /**
     * @dataProvider getPageLimitData
     */
    public function testGetPageCount(int $expected, int $totalItemCount, int $itemsPerPage, ?int $pageLimit): void
    {
        $this->pagination->setTotalItemCount($totalItemCount);
        $this->pagination->setItemNumberPerPage($itemsPerPage);
        $this->pagination->setPageLimit($pageLimit);

        $this->assertSame($expected, $this->pagination->getPageCount());
    }

    /**
     * @dataProvider getSortedData
     */
    public function testSorted(bool $expected, string $sort, string $direction, $key): void
    {
        $this->pagination->setPaginatorOptions([
            'sortFieldParameterName' => 'sort',
            'sortDirectionParameterName' => 'direction'
        ]);
        $this->pagination->setParam('sort', $sort);
        $this->pagination->setParam('direction', $direction);

        $this->assertSame($expected, $this->pagination->isSorted($key));
    }

    public function getPageLimitData(): array
    {
        return [
            'Normal' => [5, 120, 25, null],
            'No pages' => [0, 0, 25, null],
            'Pages limited to 3' => [3, 400, 25, 3],
            'Pages limited to 3, but limit not hit' => [2, 26, 25, 3],
        ];
    }

    public function getSortedData(): \Generator
    {
        yield [true, 'title', 'asc', null];
        yield [true, 'title', 'asc', 'title'];
        yield [true, 'title+subtitle', 'asc', 'title+subtitle'];
        yield [true, 'title+subtitle', 'asc', ['title', 'subtitle']];
        yield [false, 'title', 'asc', 'subtitle'];
    }
}
