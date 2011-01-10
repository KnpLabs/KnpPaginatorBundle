<?php

namespace Bundle\DoctrinePaginatorBundle\Event\Listener;

/**
 * Event listener exception list
 */
class ListenerException extends \Exception
{
    static public function eventIsNotProcessed($method)
    {
        return new self("Event was not processed in paginator adapter method: {$method}");
    }
    
    static public function queryTypeIsInvalidForManager($manager)
    {
        return new self("Query type supplied is invalid for object manager: {$manager}");
    }
    
    static public function odmQueryTypeInvalid()
    {
        return new self("ODM query must be a FIND type query");
    }
}