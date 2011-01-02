<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Symfony\Component\EventDispatcher\Event,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\WhereInWalker;

class Result extends PaginatorListener
{
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'generateQueryResult'
        );
    }
    
    public function generateQueryResult(Event $event)
    {
        $query = $event->get('query');
        if ($query instanceof Query) {
            $distinct = $event->get('distinct');
            if ($distinct) {
                $limitSubQuery = clone $query;
                $limitSubQuery->setParameters($query->getParameters());
                $limitSubQuery->setHint(
                    Query::HINT_CUSTOM_TREE_WALKERS, 
                    array('Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\LimitSubqueryWalker')
                );
                $limitSubQuery->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('count'));
                $ids = array_map('current', $limitSubQuery->getScalarResult());
                // create where-in query
                $query->setHint(
                    Query::HINT_CUSTOM_TREE_WALKERS, 
                    array('Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\WhereInWalker')
                );
                $query->setHint(WhereInWalker::HINT_PAGINATOR_ID_COUNT, count($ids))
                    ->setFirstResult(null)
                    ->setMaxResults(null);

                foreach ($ids as $i => $id) {
                    $query->setParameter(WhereInWalker::HINT_PAGINATOR_ID_ALIAS . '_' . ++$i, $id);
                }
            } else {
                $query->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('count'));
            }
            $event->setReturnValue($query->getResult());
        } else {
            throw new \RuntimeException('not orm query');
        }
        return true;
    }
}