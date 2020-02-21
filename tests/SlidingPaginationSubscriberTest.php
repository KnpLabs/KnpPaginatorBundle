<?php

namespace Knp\Bundle\PaginatorBundle\Tests;

use Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Knp\Component\Pager\Event;

/**
 * Class SlidingPaginationSubscriberTest.
 */
final class SlidingPaginationSubscriberTest extends TestCase
{
    private $options;
    private $subscriberOptions;

    public function setUp(): void
    {
        $defaultOptions = [
            PaginatorInterface::PAGE_PARAMETER_NAME => 'page',
            PaginatorInterface::SORT_FIELD_PARAMETER_NAME => 'sort',
            PaginatorInterface::SORT_DIRECTION_PARAMETER_NAME => 'direction',
            PaginatorInterface::FILTER_FIELD_PARAMETER_NAME => 'filterParam',
            PaginatorInterface::FILTER_VALUE_PARAMETER_NAME => 'filterValue',
            PaginatorInterface::DISTINCT => true,
        ];
        $options = [
            PaginatorInterface::DEFAULT_SORT_FIELD_NAME => 'p.id',
            PaginatorInterface::DEFAULT_SORT_DIRECTION => 'desc',
            PaginatorInterface::SORT_FIELD_WHITELIST => ['p.id', 'p.name'],
        ];
        $options = array_merge($defaultOptions, $options);
        $subscriberOptions = [
            'defaultPaginationTemplate' => '@KnpPaginator/Pagination/foo.html.twig',
            'defaultSortableTemplate' => '@KnpPaginator/Pagination/baz.html.twig',
            'defaultFiltrationTemplate' => '@KnpPaginator/Pagination/bar.html.twig',
            'defaultPageRange' => 5,
        ];
        $this->options = $options;
        $this->subscriberOptions = $subscriberOptions;
    }

    public function testRemoveDefaultSortParamsFalse(): void
    {
        $this->options['removeDefaultSortParams'] = false;

        $paginationEvent = new Event\PaginationEvent;
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $paginationEvent->getPagination()->getparams();

        $this->assertEquals([
            "sort" => "p.id",
            "direction" => "desc",
        ], $paginationParams);
    }

    public function testRemoveDefaultSortParamsTrue(): void
    {
        $this->options['removeDefaultSortParams'] = true;

        // pagination initialization event
        $paginationEvent = new Event\PaginationEvent;
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $paginationEvent->getPagination()->getparams();

        $this->assertEquals([], $paginationParams);
    }

    public function testRemoveDefaultSortParamsNotIsset(): void
    {
        $paginationEvent = new Event\PaginationEvent;
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $paginationEvent->getPagination()->getparams();

        $this->assertEquals([
            "sort" => "p.id",
            "direction" => "desc",
        ], $paginationParams);
    }
}
