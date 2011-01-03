<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\Helper as QueryHelper,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Countable\CountWalker,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;

/**
 * ORM Countable listener is responsible
 * for counting the query resultset
 */
class Countable extends PaginatorListener
{
    /**
     * AST Tree Walker for count operation
     */
    const TREE_WALKER_COUNT = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Countable\CountWalker';
    
    /**
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_COUNT => 'countableQuery'
        );
    }
    
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
            $countQuery = QueryHelper::cloneQuery($query, $event->getUsedHints());
            $countQuery->setParameters($query->getParameters());
            QueryHelper::addCustomTreeWalker($countQuery, self::TREE_WALKER_COUNT);
            $countQuery->setHint(
                CountWalker::HINT_PAGINATOR_COUNT_DISTINCT,
                $event->get('distinct')
            );
            $countQuery->setFirstResult(null)
                ->setMaxResults(null);
            $event->setReturnValue($countQuery->getSingleScalarResult());
        } else {
            ListenerException::queryTypeIsInvalidForManager('ORM');
        }
        return true;
    }
}