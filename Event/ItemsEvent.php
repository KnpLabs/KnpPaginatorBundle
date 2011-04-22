<?php

namespace Knplabs\Bundle\PaginatorBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Specific Event class for paginator
 */
class ItemsEvent extends Event
{
    const NAME = 'items';

    private $distinct;
    private $query;
    private $offset;
    private $numRows;
    private $items;
    private $alias;

    public function __construct($query, $distinct, $offset, $numRows, $alias)
    {
        $this->query = $query;
        $this->distinct = (bool)$distinct;
        $this->offset = $offset;
        $this->numRows = $numRows;
        $this->alias = $alias;
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

    public function getRowCountPerPage()
    {
        return $this->numRows;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }
}
