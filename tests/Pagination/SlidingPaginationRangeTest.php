<?php

declare(strict_types=1);

namespace Knp\Bundle\PaginatorBundle\Tests\Pagination;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SlidingPaginationRangeTest extends TestCase
{
    #[DataProvider('getRangeBoundaryData')]
    public function testRangeBoundaries(int $total, int $range, int $current): void
    {
        $pagination = new SlidingPagination([]);
        $pagination->setTotalItemCount($total);
        $pagination->setItemNumberPerPage(1);
        $pagination->setPageRange($range);
        $pagination->setCurrentPageNumber($current);

        // A second call also covers the existing mutation of page range/current page.
        for ($i = 0; $i < 2; ++$i) {
            $data = $pagination->getPaginationData();

            self::assertSame(\min($data['pagesInRange']), $data['firstPageInRange']);
            self::assertSame(\max($data['pagesInRange']), $data['lastPageInRange']);
        }
    }

    public static function getRangeBoundaryData(): \Generator
    {
        yield 'empty pagination' => [0, 5, 1];
        yield 'single page' => [1, 5, 1];
        yield 'beginning' => [100, 5, 1];
        yield 'middle' => [100, 5, 50];
        yield 'end' => [100, 5, 100];
        yield 'even range' => [100, 4, 50];
        yield 'range above page count' => [3, 10, 2];
        yield 'current above page count' => [10, 5, 100];
        yield 'large range' => [20000, 10000, 15000];
        yield 'zero range descends' => [100, 0, 50];
        yield 'zero range at beginning' => [100, 0, 1];
        yield 'zero total and range' => [0, 0, 1];
        yield 'negative current' => [100, 5, -10];
    }
}
