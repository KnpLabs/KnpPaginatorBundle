<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ODM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ODM\MongoDB\Query\Query,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;

/**
 * ODM Countable listener is responsible
 * for counting the query resultset
 */
class Countable extends PaginatorListener
{
    /**
     * Executes the count on Query used for
     * pagination.
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return true - defining the final count event
     */
    public function countableQuery(PaginatorEvent $event)
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
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_COUNT => 'countableQuery'
        );
    }
}