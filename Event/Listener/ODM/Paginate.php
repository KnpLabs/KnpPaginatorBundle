<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ODM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ODM\MongoDB\Query\Query,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;

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
        if ($query instanceof Query) {
            $event->setReturnValue($query->count());
        } else {
            throw ListenerException::queryTypeIsInvalidForManager('ODM');
        }
        return true;
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
        if ($query instanceof Query) {
            $type = $query->getType();
            if ($type !== Query::TYPE_FIND) {
                throw ListenerException::odmQueryTypeInvalid();
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
            $event->setReturnValue($cursor->toArray());
        } else {
            throw ListenerException::queryTypeIsInvalidForManager('ODM');
        }
        return true;
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