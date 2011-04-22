<?php

namespace Knplabs\Bundle\PaginatorBundle\Event;

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
    private $alias;

    public function __construct($query, $distinct, $alias)
    {
        $this->query = $query;
        $this->distinct = (bool)$distinct;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getAlias()
    {
        return $this->alias;
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
