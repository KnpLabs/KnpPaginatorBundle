<?php

namespace Knp\Bundle\PaginatorBundle\Subscriber;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Event\PaginationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class SlidingPaginationSubscriber implements EventSubscriberInterface
{
    private $route;
    private $params = [];
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        $this->route = $request->attributes->get('_route');
        $this->params = \array_replace($request->query->all(), $request->attributes->get('_route_params', []));
        foreach ($this->params as $key => $param) {
            if ('_' == \substr($key, 0, 1)) {
                unset($this->params[$key]);
            }
        }
    }

    public function pagination(PaginationEvent $event): void
    {
        // default sort field and order
        $eventOptions = $event->options;

        if (isset($eventOptions['defaultSortFieldName']) && !isset($this->params[$eventOptions['sortFieldParameterName']])) {
            $this->params[$eventOptions['sortFieldParameterName']] = $eventOptions['defaultSortFieldName'];
        }

        if (isset($eventOptions['defaultSortDirection']) && !isset($this->params[$eventOptions['sortDirectionParameterName']])) {
            $this->params[$eventOptions['sortDirectionParameterName']] = $eventOptions['defaultSortDirection'];
        }

        // remove default sort params from pagination links
        if (isset($eventOptions['removeDefaultSortParams']) && true === $eventOptions['removeDefaultSortParams']) {
            $defaultSortFieldName = $eventOptions['defaultSortFieldName'];
            $sortFieldParameterName = $this->params[$eventOptions['sortFieldParameterName']];
            $isFieldEqual = $defaultSortFieldName === $sortFieldParameterName;
            $defaultSortDirection = $eventOptions['defaultSortDirection'];
            $sortDirectionParameterName = $this->params[$eventOptions['sortDirectionParameterName']];
            $isDirectionEqual = $defaultSortDirection === $sortDirectionParameterName;

            if (isset($defaultSortFieldName) && isset($sortFieldParameterName) && $isFieldEqual
                && isset($defaultSortDirection) && isset($sortDirectionParameterName) && $isDirectionEqual) {
                unset($this->params[$eventOptions['sortFieldParameterName']]);
                unset($this->params[$eventOptions['sortDirectionParameterName']]);
            }
        }

        $pagination = new SlidingPagination($this->params);

        $pagination->setUsedRoute($this->route);
        $pagination->setTemplate($this->options['defaultPaginationTemplate']);
        $pagination->setSortableTemplate($this->options['defaultSortableTemplate']);
        $pagination->setFiltrationTemplate($this->options['defaultFiltrationTemplate']);
        $pagination->setPageRange($this->options['defaultPageRange']);
        $pagination->setPageLimit($this->options['defaultPageLimit']);

        $event->setPagination($pagination);
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.pagination' => ['pagination', 1],
        ];
    }
}
