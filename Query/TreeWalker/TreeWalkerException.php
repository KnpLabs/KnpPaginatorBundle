<?php

namespace Bundle\DoctrinePaginatorBundle\Query\TreeWalker;

/**
 * Query TreeWalker exception list
 */
class TreeWalkerException extends \Exception
{
    static public function invalidSortKeyAlias($alias)
    {
        return new self("There is no component aliased by [{$alias}] in the given Query");
    }
    
    static public function invalidSortKeyField($field, $alias)
    {
        return new self("There is no such field [{$field}] in the given Query component aliased like [$alias]");
    }
}