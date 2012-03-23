<?php

namespace Knp\Bundle\PaginatorBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SlidingPaginationSubscriber implements EventSubscriberInterface
{
    private $routerHelper;
    private $route;
    private $params = array();
    private $translator;
    private $engine;
    private $options;

    public function __construct(EngineInterface $engine, RouterHelper $routerHelper, TranslatorInterface $translator, array $options)
    {
        $this->routerHelper = $routerHelper;
        $this->engine = $engine;
        $this->translator = $translator;
        $this->options = $options;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $request = $event->getRequest();

            $this->route = $request->attributes->get('_route');
            $this->params = array_merge($request->query->all(), $request->attributes->all());
            foreach ($this->params as $key => $param) {
                if (substr($key, 0, 1) == '_') {
                    unset($this->params[$key]);
                }
            }
        }
    }

    public function pagination(PaginationEvent $event)
    {
        $pagination = new SlidingPagination(
            $this->engine,
            $this->routerHelper,
            $this->translator,
            $this->params
        );
        $pagination->setUsedRoute($this->route);
        $pagination->setTemplate($this->options['defaultPaginationTemplate']);
        $pagination->setSortableTemplate($this->options['defaultSortableTemplate']);
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
