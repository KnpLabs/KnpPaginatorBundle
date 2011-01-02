<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener;

use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class PaginatorListener
{
    const EVENT_COUNT = 'doctrine_paginator.count';
    const EVENT_ITEMS = 'doctrine_paginator.items';
    
    abstract protected function getEvents();
    
    public function subscribe(EventDispatcher $dispacher, $priority = 0)
    {
        foreach ($this->getEvents() as $event => $callback) {
            $dispacher->connect($event, array($this, $callback), $priority);
        }
    }
}