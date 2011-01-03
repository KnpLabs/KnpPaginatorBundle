<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Bundle\DoctrinePaginatorBundle\Query\Helper as QueryHelper,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\WhereInWalker,
    Bundle\DoctrinePaginatorBundle\Event\Listener\ListenerException;
 
/**
 * ORM Result listener is responsible
 * for generating the resultset on query supplied
 */
class Result extends PaginatorListener
{
    /**
     * AST Tree Walker for primary key retrieval in case of distinct mode
     */
    const TREE_WALKER_LIMIT_SUBQUERY = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\LimitSubqueryWalker';
    
    /**
     * AST Tree Walker for loading the resultset by primary keys in case of distinct mode
     */
    const TREE_WALKER_WHERE_IN = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Result\WhereInWalker';
    
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
            $distinct = $event->get('distinct');
            $result = null;
            if ($distinct) {
                $limitSubQuery = QueryHelper::cloneQuery($query, $event->getUsedHints());
                $limitSubQuery->setParameters($query->getParameters());
                QueryHelper::addCustomTreeWalker($limitSubQuery, self::TREE_WALKER_LIMIT_SUBQUERY);

                $limitSubQuery->setFirstResult($event->get('offset'))
                    ->setMaxResults($event->get('count'));
                $ids = array_map('current', $limitSubQuery->getScalarResult());
                // create where-in query
                $whereInQuery = QueryHelper::cloneQuery($query, $event->getUsedHints());
                QueryHelper::addCustomTreeWalker($whereInQuery, self::TREE_WALKER_WHERE_IN);
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
            ListenerException::queryTypeIsInvalidForManager('ORM');
        }
        return true;
    }
}