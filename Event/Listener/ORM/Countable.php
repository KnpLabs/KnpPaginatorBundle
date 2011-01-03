<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\Helper,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Countable\CountWalker;

class Countable extends PaginatorListener
{
    const TREE_WALKER_COUNT = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Countable\CountWalker';
    
    protected function getEvents()
    {
        return array(
            self::EVENT_COUNT => 'countableQuery'
        );
    }
    
    public function countableQuery(PaginatorEvent $event)
    {
        $query = $event->get('query');
        if ($query instanceof Query) {
            $countQuery = Helper::cloneQuery($query, $event->getUsedHints());
            $countQuery->setParameters($query->getParameters());
            Helper::addCustomTreeWalker($countQuery, self::TREE_WALKER_COUNT);
            $countQuery->setHint(
                CountWalker::HINT_PAGINATOR_COUNT_DISTINCT,
                $event->get('distinct')
            );
            $countQuery->setFirstResult(null)
                ->setMaxResults(null);
            $event->setReturnValue($countQuery->getSingleScalarResult());
        } else {
            throw new \RuntimeException('not orm query');
        }
        return true;
    }
}