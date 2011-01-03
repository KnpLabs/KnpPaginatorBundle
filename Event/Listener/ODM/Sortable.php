<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ODM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException,
    Doctrine\ODM\MongoDB\Query;

/**
 * ODM Sortable listener is responsible
 * for sorting the resultset by request
 * query parameters
 */
class Sortable extends PaginatorListener
{
    /**
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'sort'
        );
    }
    
    /**
     * Adds a sorting to the query if request
     * parameters were set for sorting
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return void
     */
    public function sort(PaginatorEvent $event)
    {
        $request = $event->get('request');
        $params = $request->query->all();

        if (isset($params['sort'])) {
            $query = $event->get('query');
            if ($query instanceof Query) {
                // not implemmented yet
            } else {
                ListenerException::queryTypeIsInvalidForManager('ODM');
            }
        }
    }
}