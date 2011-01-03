<?php

namespace Bundle\DoctrinePaginatorBundle\Query;

use Doctrine\ORM\Query;

class Helper
{
    public static function cloneQuery(Query $query, array $usedHints = array())
    {
        $clonedQuery = clone $query;
        $clonedQuery->setParameters($query->getParameters());
        // attach hints
        foreach ($usedHints as $name) {
            if (($hint = $query->getHint($name)) !== false) {
                $clonedQuery->setHint($name, $hint);
            }
        }
        return $clonedQuery;
    }
    
    public static function addCustomTreeWalker(Query $query, $walker)
    {
        $customTreeWalkers = $query->getHint(Query::HINT_CUSTOM_TREE_WALKERS);
        if ($customTreeWalkers !== false && is_array($customTreeWalkers)) {
            $customTreeWalkers = array_merge($customTreeWalkers, array($walker));
        } else {
            $customTreeWalkers = array($walker);
        }
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $customTreeWalkers);
    } 
}