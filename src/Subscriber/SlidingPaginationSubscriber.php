<?php

namespace Knp\Bundle\PaginatorBundle\Subscriber;

use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Event\PaginationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class SlidingPaginationSubscriber implements EventSubscriberInterface
{
    private ?string $route = null;

    /** @var array<string, mixed> */
    private array $params = [];

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        $this->route = $request->attributes->get('_route');
        $this->params = \array_replace($request->query->all(), $request->attributes->get('_route_params', []));
        foreach ($this->params as $key => $param) {
            if (\str_starts_with($key, '_')) {
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

            if (isset($defaultSortFieldName, $sortFieldParameterName, $defaultSortDirection, $sortDirectionParameterName) && $isFieldEqual && $isDirectionEqual) {
                unset($this->params[$eventOptions['sortFieldParameterName']], $this->params[$eventOptions['sortDirectionParameterName']]);
            }
        }

        $pagination = new SlidingPagination($this->params);

        $pagination->setUsedRoute($this->route);
        $pagination->setTemplate($this->options['defaultPaginationTemplate']);
        $pagination->setRelLinksTemplate($this->options['defaultRelLinksTemplate']);
        $pagination->setSortableTemplate($this->options['defaultSortableTemplate']);
        $pagination->setFiltrationTemplate($this->options['defaultFiltrationTemplate']);
        $pagination->setPageRange($this->options['defaultPageRange']);
        $pagination->setPageLimit($this->options['defaultPageLimit']);

        $event->setPagination($pagination);
        $event->stopPropagation();
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.pagination' => ['pagination', 1],
        ];
    }
}
