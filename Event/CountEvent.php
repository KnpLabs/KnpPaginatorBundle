<?php

namespace Knplabs\PaginatorBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Specific Event class for paginator
 */
class CountEvent extends Event
{
    const NAME = 'count';
    
    private $distinct;
    private $query;
    private $count;
    
    public function __construct($query, $distinct)
    {
        $this->query = $query;
        $this->distinct = (bool)$distinct;
    }
    
    public function getQuery()
    {
        return $this->query;
    }
    
    public function isDistinct()
    {
        return $this->distinct;
    }
    
    public function setCount($count)
    {
        $this->count = intval($count);
    }
    
    public function getCount()
    {
        return $this->count;
    }
}