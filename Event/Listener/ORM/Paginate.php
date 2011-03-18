<?php

namespace Knplabs\PaginatorBundle\Event\Listener\ORM;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Knplabs\PaginatorBundle\Event\CountEvent,
    Knplabs\PaginatorBundle\Event\ItemsEvent,
    Knplabs\PaginatorBundle\Query\Helper as QueryHelper,
    Knplabs\PaginatorBundle\Query\TreeWalker\Paginate\CountWalker,
    Knplabs\PaginatorBundle\Query\TreeWalker\Paginate\WhereInWalker,
    Doctrine\ORM\Query;

/**
 * ORM Paginate listener is responsible
 * for standard pagination of query resultset
 */
class Paginate implements EventSubscriberInterface
{
    /**
     * AST Tree Walker for count operation
     */
    const TREE_WALKER_COUNT = 'Knplabs\PaginatorBundle\Query\TreeWalker\Paginate\CountWalker';
    
    /**
     * AST Tree Walker for primary key retrieval in case of distinct mode
     */
    const TREE_WALKER_LIMIT_SUBQUERY = 'Knplabs\PaginatorBundle\Query\TreeWalker\Paginate\LimitSubqueryWalker';
    
    /**
     * AST Tree Walker for loading the resultset by primary keys in case of distinct mode
     */
    const TREE_WALKER_WHERE_IN = 'Knplabs\PaginatorBundle\Query\TreeWalker\Paginate\WhereInWalker';
    
    /**
     * Executes the count on Query used for
     * pagination.
     * 
     * @param CountEvent $event
     * @throws ListenerException - if query supplied is invalid
     */
    public function count(CountEvent $event)
    {
        $query = $event->getQuery();
        $countQuery = QueryHelper::cloneQuery($query);
        $countQuery->setParameters($query->getParameters());
        QueryHelper::addCustomTreeWalker($countQuery, self::TREE_WALKER_COUNT);
        $countQuery->setHint(
            CountWalker::HINT_PAGINATOR_COUNT_DISTINCT,
            $event->isDistinct()
        );
        $countQuery->setFirstResult(null)
            ->setMaxResults(null);
        $event->stopPropagation();
        $countResult = $countQuery->getResult(Query::HYDRATE_ARRAY);
        $event->setCount(count($countResult) > 1 ? count($countResult) : current(current($countResult)));
    }
    
    /**
     * Generates the paginated resultset
     * 
     * @param ItemsEvent $event
     * @throws ListenerException - if query supplied is invalid
     */
    public function items(ItemsEvent $event)
    {
        $query = $event->getQuery();
        $distinct = $event->isDistinct();
        $result = null;
        if ($distinct) {
            $limitSubQuery = QueryHelper::cloneQuery($query);
            $limitSubQuery->setParameters($query->getParameters());
            QueryHelper::addCustomTreeWalker($limitSubQuery, self::TREE_WALKER_LIMIT_SUBQUERY);

            $limitSubQuery->setFirstResult($event->getOffset())
                ->setMaxResults($event->getRowCountPerPage());
            $ids = array_map('current', $limitSubQuery->getScalarResult());
            // create where-in query
            $whereInQuery = QueryHelper::cloneQuery($query);
            QueryHelper::addCustomTreeWalker($whereInQuery, self::TREE_WALKER_WHERE_IN);
            $whereInQuery->setHint(WhereInWalker::HINT_PAGINATOR_ID_COUNT, count($ids))
                ->setFirstResult(null)
                ->setMaxResults(null);

            foreach ($ids as $i => $id) {
                $whereInQuery->setParameter(WhereInWalker::PAGINATOR_ID_ALIAS . '_' . ++$i, $id);
            }
            $result = $whereInQuery->getResult();
        } else {
            $query->setFirstResult($event->getOffset())
                ->setMaxResults($event->getRowCountPerPage());
            $result = $query->getResult();
        }
        $event->stopPropagation();
        $event->setItems($result);
    }
    
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ItemsEvent::NAME,
            CountEvent::NAME
        );
    }
}