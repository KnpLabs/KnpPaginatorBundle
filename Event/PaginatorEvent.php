<?php

namespace Bundle\DoctrinePaginatorBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class PaginatorEvent extends Event
{
    private $queryHints = array();
    
    public function addUsedHint($name)
    {
        if (!in_array($name, $this->queryHints)) {
            $this->queryHints[] = $name;
        }
        return $this;
    }
    
    public function getUsedHints()
    {
        return $this->queryHints;
    }
}