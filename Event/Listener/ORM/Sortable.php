<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Symfony\Component\EventDispatcher\Event,
    Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\OrderByWalker,
    Doctrine\ORM\Query;

class Sortable extends PaginatorListener
{
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'sort'
        );
    }
    
    public function sort(Event $event)
    {
        //echo 'here';
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
                $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS, current($parts))
                    ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION, $params['direction'])
                    ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELD, end($parts))
                    ->setHint(
                        Query::HINT_CUSTOM_TREE_WALKERS, 
                        array('Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM\OrderByWalker')
                    );
                //
            } else {
                throw new \RuntimeException('not orm query');
            }
        }
        //die('called');
    }
}