<?php

namespace Knplabs\PaginatorBundle\Event;

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
    
    public function __construct($query, $distinct, $offset, $numRows)
    {
        $this->query = $query;
        $this->distinct = (bool)$distinct;
        $this->offset = $offset;
        $this->numRows = $numRows;
    }
    
    public function getQuery()
    {
        return $this->query;
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