<?php

namespace Knp\Bundle\PaginatorBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SlidingPaginationSubscriber implements EventSubscriberInterface
{
    private $route;
    private $params = array();
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        $this->route = $request->attributes->get('_route');
        $this->params = array_merge($request->query->all(), $request->attributes->all());
        foreach ($this->params as $key => $param) {
            if (substr($key, 0, 1) == '_') {
                unset($this->params[$key]);
            }
        }
    }

    public function pagination(PaginationEvent $event)
    {
        $pagination = new SlidingPagination($this->params);

        $pagination->setUsedRoute($this->route);
        $pagination->setTemplate($this->options['defaultPaginationTemplate']);
        $pagination->setSortableTemplate($this->options['defaultSortableTemplate']);
        $pagination->setFiltrationTemplate($this->options['defaultFiltrationTemplate']);
        $pagination->setPageRange($this->options['defaultPageRange']);

        $event->setPagination($pagination);
        $event->stopPropagation();
    }

    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.pagination' => array('pagination', 1)
        );
    }
}
