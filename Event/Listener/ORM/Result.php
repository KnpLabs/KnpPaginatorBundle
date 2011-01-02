<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Symfony\Component\EventDispatcher\Event,
    Doctrine\ORM\Query;

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
                    array('Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\CountWalker')
                );
                $countQuery->setHint(
                    CountWalker::HINT_COUNT_DISTINCT,
                    $event->get('distinct')
                );
            }
        } else {
            throw new \RuntimeException('not orm query');
        }
        return true;
    }
}