<?php

namespace Knp\Bundle\PaginatorBundle\Tests;

use Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber;
use Knp\Component\Pager\Event;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class SlidingPaginationSubscriberTest.
 */
final class SlidingPaginationSubscriberTest extends TestCase
{
    private $options;
    private $subscriberOptions;

    protected function setUp(): void
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
            PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['p.id', 'p.name'],
        ];
        $options = \array_merge($defaultOptions, $options);
        $subscriberOptions = [
            'defaultPaginationTemplate' => '@KnpPaginator/Pagination/foo.html.twig',
            'defaultSortableTemplate' => '@KnpPaginator/Pagination/baz.html.twig',
            'defaultFiltrationTemplate' => '@KnpPaginator/Pagination/bar.html.twig',
            'defaultPageRange' => 5,
            'defaultPageLimit' => null,
        ];
        $this->options = $options;
        $this->subscriberOptions = $subscriberOptions;
    }

    public function testRemoveDefaultSortParamsFalse(): void
    {
        $this->options['removeDefaultSortParams'] = false;

        $paginationEvent = new Event\PaginationEvent();
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $this->getPagination($paginationEvent)->getParams();

        $this->assertEquals([
            'sort' => 'p.id',
            'direction' => 'desc',
        ], $paginationParams);
    }

    public function testRemoveDefaultSortParamsTrue(): void
    {
        $this->options['removeDefaultSortParams'] = true;

        // pagination initialization event
        $paginationEvent = new Event\PaginationEvent();
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $this->getPagination($paginationEvent)->getParams();

        $this->assertEquals([], $paginationParams);
    }

    public function testRemoveDefaultSortParamsNotIsset(): void
    {
        $paginationEvent = new Event\PaginationEvent();
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $this->getPagination($paginationEvent)->getParams();

        $this->assertEquals([
            'sort' => 'p.id',
            'direction' => 'desc',
        ], $paginationParams);
    }

    public function testPageLimitSet(): void
    {
        $this->subscriberOptions['defaultPageLimit'] = 50;

        $paginationEvent = new Event\PaginationEvent();
        $paginationEvent->options = &$this->options;

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $pagination = $this->getPagination($paginationEvent);

        $pagination->setItemNumberPerPage(1);
        $pagination->setTotalItemCount(49);

        $this->assertSame(49, $pagination->getPageCount());

        $pagination->setTotalItemCount(51);

        $this->assertSame(50, $pagination->getPageCount());
    }

    public function testRequestParams(): void
    {
        $query = [
            '_hash' => 'abcdef',
            '123' => 'integer key',
            'page' => '2',
        ];
        $attributes = [
            '_route_params' => [
                '_locale' => 'en',
                '123' => 'integer key from _route_params',
                'some_route_param' => 'something',
            ],
        ];

        $this->options['removeDefaultSortParams'] = true;

        $paginationEvent = new Event\PaginationEvent();
        $paginationEvent->options = &$this->options;

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request($query, [], $attributes);
        $requestEvent = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $slidingPaginationSubscriber = new SlidingPaginationSubscriber($this->subscriberOptions);
        $slidingPaginationSubscriber->onKernelRequest($requestEvent);
        $slidingPaginationSubscriber->pagination($paginationEvent);
        $paginationParams = $this->getPagination($paginationEvent)->getParams();

        $this->assertEquals([
            '123' => 'integer key from _route_params',
            'page' => '2',
            'some_route_param' => 'something',
        ], $paginationParams);
    }

    /**
     * @return \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination
     */
    private function getPagination(Event\PaginationEvent $event): PaginationInterface
    {
        return $event->getPagination();
    }
}
