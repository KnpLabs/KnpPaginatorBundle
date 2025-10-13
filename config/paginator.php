<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationRuntime;
use Knp\Component\Pager\ArgumentAccess\ArgumentAccessInterface;
use Knp\Component\Pager\ArgumentAccess\RequestArgumentAccess;
use Knp\Component\Pager\Event\Subscriber\Filtration\FiltrationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set('knp_paginator', Paginator::class)
        ->public()
        ->lazy()
        ->args([
            service('event_dispatcher'),
            service(ArgumentAccessInterface::class),
            service('database_connection')->nullOnInvalid(),
        ])
        ->tag('proxy', ['interface' => PaginatorInterface::class])
    ;

    $services->alias(PaginatorInterface::class, 'knp_paginator');

    $services
        ->set(RequestArgumentAccess::class)
        ->args([service('request_stack')])
    ;

    $services->alias(ArgumentAccessInterface::class, RequestArgumentAccess::class);

    $services
        ->set('knp_paginator.subscriber.paginate', PaginationSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;

    $services
        ->set('knp_paginator.subscriber.sortable', SortableSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;

    $services
        ->set('knp_paginator.subscriber.filtration', FiltrationSubscriber::class)
        ->tag('kernel.event_subscriber')
    ;

    $services->set('knp_paginator.subscriber.sliding_pagination', SlidingPaginationSubscriber::class)
        ->arg(0, [
            'defaultPaginationTemplate' => '%knp_paginator.template.pagination%',
            'defaultRelLinksTemplate' => '%knp_paginator.template.rel_links%',
            'defaultSortableTemplate' => '%knp_paginator.template.sortable%',
            'defaultFiltrationTemplate' => '%knp_paginator.template.filtration%',
            'defaultPageRange' => '%knp_paginator.page_range%',
            'defaultPageLimit' => '%knp_paginator.page_limit%',
        ])
        ->tag('kernel.event_subscriber')
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest'])
    ;

    $services
        ->set('knp_paginator.helper.processor', Processor::class)
        ->args([
            service('router'),
            service('translator')->nullOnInvalid(),
        ])
    ;

    $services
        ->set('knp_paginator.twig.extension.pagination', PaginationExtension::class)
        ->tag('twig.extension')
    ;

    $services
        ->set(PaginationRuntime::class)
        ->args([
            service('knp_paginator.helper.processor'),
            param('knp_paginator.page_name'),
            param('knp_paginator.remove_first_page_param'),
        ])
        ->tag('twig.runtime')
    ;
};
