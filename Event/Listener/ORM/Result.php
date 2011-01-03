<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Bundle\DoctrinePaginatorBundle\Query\Helper,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\WhereInWalker;

class Result extends PaginatorListener
{
    const TREE_WALKER_LIMIT_SUBQUERY = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\LimitSubqueryWalker';
    const TREE_WALKER_WHERE_IN = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\WhereInWalker';
    
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'generateQueryResult'
        );
    }
    
    public function generateQueryResult(PaginatorEvent $event)
    {
        $query = $event->get('query');
        if ($query instanceof Query) {
            $distinct = $event->get('distinct');
            $result = null;
            if ($distinct) {
                $limitSubQuery = Helper::cloneQuery($query, $event->getUsedHints());
                $limitSubQuery->setParameters($query->getParameters());
                Helper::addCustomTreeWalker($limitSubQuery, self::TREE_WALKER_LIMIT_SUBQUERY);

                $limitSubQuery->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('count'));
                $ids = array_map('current', $limitSubQuery->getScalarResult());
                // create where-in query
                $whereInQuery = Helper::cloneQuery($query, $event->getUsedHints());
                Helper::addCustomTreeWalker($whereInQuery, self::TREE_WALKER_WHERE_IN);
                $whereInQuery->setHint(WhereInWalker::HINT_PAGINATOR_ID_COUNT, count($ids))
                    ->setFirstResult(null)
                    ->setMaxResults(null);

                foreach ($ids as $i => $id) {
                    $whereInQuery->setParameter(WhereInWalker::HINT_PAGINATOR_ID_ALIAS . '_' . ++$i, $id);
                }
                $result = $whereInQuery->getResult();
            } else {
                $query->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('count'));
                $result = $query->getResult();
            }
            $event->setReturnValue($result);
        } else {
            throw new \RuntimeException('not orm query');
        }
        return true;
    }
}