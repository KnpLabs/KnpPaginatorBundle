<?php

namespace Bundle\DoctrinePaginatorBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Specific Event class for paginator to track
 * query hints used since it is yet not possible
 * to clone query with all hints specified.
 */
class PaginatorEvent extends Event
{
    /**
     * Hints used for this event Query parameter
     * 
     * @var array
     */
    private $usedHints = array();
    
    /**
     * Track the hint used
     * 
     * @param string $name
     * @return Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent
     */
    public function addUsedHint($name)
    {
        if (!in_array($name, $this->usedHints)) {
            $this->usedHints[] = $name;
        }
        return $this;
    }
    
    /**
     * Get all hints used for this event so far
     * 
     * @return array
     */
    public function getUsedHints()
    {
        return $this->usedHints;
    }
}