<?php

namespace Knplabs\PaginatorBundle\Event\Listener\ODM;

use Knplabs\PaginatorBundle\Event\ItemsEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Knplabs\PaginatorBundle\Event\Listener\ListenerException,
    Doctrine\ODM\MongoDB\Query\Query,
    Symfony\Component\HttpFoundation\Request;

/**
 * ODM Sortable listener is responsible
 * for sorting the resultset by request
 * query parameters
 */
class Sortable implements EventSubscriberInterface
{
    /**
     * Current request
     * 
     * @var Request
     */
    protected $request = null;
    
    /**
     * Initialize with requests
     * 
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Adds a sorting to the query if request
     * parameters were set for sorting
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return void
     */
    public function items(ItemsEvent $event)
    {
        $params = $this->request->query->all();

        if (isset($params['sort'])) {
            $query = $event->getQquery();
            $field = $params['sort'];
            $direction = strtolower($params['direction']) == 'asc' ? 1 : -1;
            
            $reflClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
            $reflProp = $reflClass->getProperty('query');
            $reflProp->setAccessible(true);
            $queryOptions = $reflProp->getValue($query);
            
            $queryOptions['sort'][$field] = $direction;
            $reflProp->setValue($query, $queryOptions);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ItemsEvent::NAME
        );
    }
}