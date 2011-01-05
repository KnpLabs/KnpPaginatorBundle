<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator;

/**
 * Doctrine Paginator Adapter exception list
 */
class AdapterException extends \Exception
{
    static public function invalidQuery($class)
    {
        return new self("The query supplied must be ORM or ODM Query object, [$class] given");
    }
    
    static public function queryIsMissing()
    {
        return new self("Paginator Query must be supplied at this point");
    }
}