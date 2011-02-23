<?php

namespace Knplabs\PaginatorBundle\Event\Listener\ODM;

use Knplabs\PaginatorBundle\Event\Listener\PaginatorListener,
    Knplabs\PaginatorBundle\Event\PaginatorEvent,
    Doctrine\ODM\MongoDB\Query\Query,
    Knplabs\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * ODM Paginate listener is responsible
 * for standard pagination of query resultset
 */
class Paginate extends PaginatorListener
{
    /**
     * Executes the count on Query used for
     * pagination.
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return true - defining the final count event
     */
    public function onQueryCount(PaginatorEvent $event)
    {
        $query = $event->get('query');
        return $query->count();
    }
    
    /**
     * Generates the paginated resultset
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return true - defining the final items event
     */
    public function onQueryResult(PaginatorEvent $event)
    {
        $query = $event->get('query');
        $type = $query->getType();
        if ($type !== Query::TYPE_FIND) {
            throw new UnexpectedValueException('ODM query must be a FIND type query');
        }
        $reflClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
        $reflProp = $reflClass->getProperty('query');
        $reflProp->setAccessible(true);
        $queryOptions = $reflProp->getValue($query);
        
        $queryOptions['limit'] = $event->get('numRows');
        $queryOptions['skip'] = $event->get('offset');
        
        $resultQuery = clone $query;
        $reflProp->setValue($resultQuery, $queryOptions);
        $cursor = $resultQuery->execute();

        $event->setProcessed();
        return $cursor->toArray();
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_COUNT => 'onQueryCount',
            self::EVENT_ITEMS => 'onQueryResult'
        );
    }
}