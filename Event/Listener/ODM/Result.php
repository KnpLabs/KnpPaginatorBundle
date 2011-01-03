<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ODM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ODM\MongoDB\Query,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;
 
/**
 * ODM Result listener is responsible
 * for generating the resultset on query supplied
 */
class Result extends PaginatorListener
{ 
    /**
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'generateQueryResult'
        );
    }
    
    /**
     * Generates the paginated resultset
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return true - defining the final items event
     */
    public function generateQueryResult(PaginatorEvent $event)
    {
        $query = $event->get('query');
        if ($query instanceof Query) {
            // not implemmented yet
        } else {
            ListenerException::queryTypeIsInvalidForManager('ODM');
        }
        return true;
    }
}