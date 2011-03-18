<?php

namespace Knplabs\PaginatorBundle\Event\Listener\ODM;

use Knplabs\PaginatorBundle\Event\CountEvent,
    Knplabs\PaginatorBundle\Event\ItemsEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Doctrine\ODM\MongoDB\Query\Query,
    Knplabs\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * ODM Paginate listener is responsible
 * for standard pagination of query resultset
 */
class Paginate implements EventSubscriberInterface
{
    /**
     * Executes the count on Query used for
     * pagination.
     * 
     * @param CountEvent $event
     * @throws ListenerException - if query supplied is invalid
     */
    public function count(CountEvent $event)
    {
        $query = $event->getQquery();
        $event->stopPropagation();
        $event->setCount($query->count());
    }
    
    /**
     * Generates the paginated resultset
     * 
     * @param ItemsEvent $event
     * @throws ListenerException - if query supplied is invalid
     */
    public function items(ItemsEvent $event)
    {
        $query = $event->getQuery();
        $type = $query->getType();
        if ($type !== Query::TYPE_FIND) {
            throw new UnexpectedValueException('ODM query must be a FIND type query');
        }
        $reflClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
        $reflProp = $reflClass->getProperty('query');
        $reflProp->setAccessible(true);
        $queryOptions = $reflProp->getValue($query);
        
        $queryOptions['limit'] = $event->getRowCountPerPage();
        $queryOptions['skip'] = $event->getOffset();
        
        $resultQuery = clone $query;
        $reflProp->setValue($resultQuery, $queryOptions);
        $cursor = $resultQuery->execute();

        $event->stopPropagation();
        $event->setItems($cursor->toArray());
    }
    
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            CountEvent::NAME,
            ItemsEvent::NAME
        );
    }
}