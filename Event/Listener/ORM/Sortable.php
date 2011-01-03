<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Bundle\DoctrinePaginatorBundle\Query\Helper,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker,
    Doctrine\ORM\Query;

class Sortable extends PaginatorListener
{
    const TREE_WALKER_ORDER_BY = 'Bundle\DoctrinePaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker';
    
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'sort'
        );
    }
    
    public function sort(PaginatorEvent $event)
    {
        $request = $event->get('request');
        $params = $request->query->all();
        //die(print_r($params));
        if (isset($params['sort'])) {
            $query = $event->get('query');
            if ($query instanceof Query) {
                $parts = explode('.', $params['sort']);
                if (count($parts) != 2) {
                    throw new \RuntimeException('invalid sort key');
                }
                $event->addUsedHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS)
                    ->addUsedHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION)
                    ->addUsedHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELD)
                    ->addUsedHint(Query::HINT_CUSTOM_TREE_WALKERS);
                
                $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS, current($parts))
                    ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION, $params['direction'])
                    ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELD, end($parts));
                Helper::addCustomTreeWalker($query, self::TREE_WALKER_ORDER_BY);
            } else {
                throw new \RuntimeException('not orm query');
            }
        }
        //die('called');
    }
}