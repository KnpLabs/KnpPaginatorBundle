<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener\ORM;

use Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Symfony\Component\EventDispatcher\Event;

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
        $request = $event->get('request');
        
        $params = $request->query->all();
        if (isset($params['sort'])) {
            $query = $event->get('query');
            //'article.title'
            $parts = explode('.', $params['sort']);
            $field = end($parts);
            $alias = count($parts) > 1 ? $parts[0] : null;
        }
        //die('called');
    }
}