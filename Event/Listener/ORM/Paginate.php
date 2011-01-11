<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\Helper as QueryHelper,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Paginate\CountWalker,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Paginate\WhereInWalker,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;

/**
 * ORM Paginate listener is responsible
 * for standard pagination of query resultset
 */
class Paginate extends PaginatorListener
{
    /**
     * AST Tree Walker for count operation
     */
    const TREE_WALKER_COUNT = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Paginate\CountWalker';
    
    /**
     * AST Tree Walker for primary key retrieval in case of distinct mode
     */
    const TREE_WALKER_LIMIT_SUBQUERY = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Paginate\LimitSubqueryWalker';
    
    /**
     * AST Tree Walker for loading the resultset by primary keys in case of distinct mode
     */
    const TREE_WALKER_WHERE_IN = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Paginate\WhereInWalker';
    
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
            throw ListenerException::queryTypeIsInvalidForManager('ORM');
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
            $distinct = $event->get('distinct');
            $result = null;
            if ($distinct) {
                $limitSubQuery = QueryHelper::cloneQuery($query, $event->getUsedHints());
                $limitSubQuery->setParameters($query->getParameters());
                QueryHelper::addCustomTreeWalker($limitSubQuery, self::TREE_WALKER_LIMIT_SUBQUERY);

                $limitSubQuery->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('numRows'));
                $ids = array_map('current', $limitSubQuery->getScalarResult());
                // create where-in query
                $whereInQuery = QueryHelper::cloneQuery($query, $event->getUsedHints());
                QueryHelper::addCustomTreeWalker($whereInQuery, self::TREE_WALKER_WHERE_IN);
                $whereInQuery->setHint(WhereInWalker::HINT_PAGINATOR_ID_COUNT, count($ids))
                    ->setFirstResult(null)
                    ->setMaxResults(null);

                foreach ($ids as $i => $id) {
                    $whereInQuery->setParameter(WhereInWalker::PAGINATOR_ID_ALIAS . '_' . ++$i, $id);
                }
                $result = $whereInQuery->getResult();
            } else {
                $query->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('numRows'));
                $result = $query->getResult();
            }
            $event->setReturnValue($result);
        } else {
            throw ListenerException::queryTypeIsInvalidForManager('ORM');
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