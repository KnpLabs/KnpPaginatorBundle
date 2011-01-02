<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Symfony\Component\EventDispatcher\Event,
    Doctrine\ORM\Query,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\CountWalker;

class Countable extends PaginatorListener
{
    protected function getEvents()
    {
        return array(
            self::EVENT_COUNT => 'countableQuery'
        );
    }
    
    public function countableQuery(Event $event)
    {
        $query = $event->get('query');
        if ($query instanceof Query) {
            $countQuery = clone $query;
            $countQuery->setParameters($query->getParameters());
            $countQuery->setHint(
                Query::HINT_CUSTOM_TREE_WALKERS, 
                array('Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\CountWalker')
            );
            $countQuery->setHint(
                CountWalker::HINT_COUNT_DISTINCT,
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