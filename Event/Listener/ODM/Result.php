<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ODM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ODM\MongoDB\Query\Query,
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
            $type = $query->getType();
            if ($type !== Query::TYPE_FIND) {
                throw ListenerException::odmQueryTypeInvalid();
            }
            $resultQuery = clone $query;
            $cursor = $resultQuery->execute();
            $cursor->skip($event->get('offset'))
                ->limit($event->get('numRows'));
            $event->setReturnValue($cursor);
        } else {
            throw ListenerException::queryTypeIsInvalidForManager('ODM');
        }
        return true;
    }
}