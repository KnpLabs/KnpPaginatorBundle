<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator;

/**
 * Doctrine Paginator Adapter exception list
 */
class AdapterException extends \Exception
{
    static public function eventIsNotProcessed($method)
    {
        return new self("Event was not processed in paginator adapter method: {$method}");
    }
    
    static public function queryIsMissing()
    {
        return new self("Paginator Query must be supplied at this point");
    }
}