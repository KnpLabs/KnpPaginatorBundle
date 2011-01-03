<?php

namespace Bundle\DoctrinePaginatorBundle\Query;

use Doctrine\ORM\Query;

/**
 * ORM Query helper for cloning
 * and hint processing
 */
class Helper
{
    /**
     * Clones the given $query and copies all used
     * parameters together with specified $usedHints
     * 
     * @param Query $query
     * @param array $usedHints
     * @return Query
     */
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
    
    /**
     * Add a custom TreeWalker $walker class name to
     * be included in the CustomTreeWalker hint list
     * of the given $query
     * 
     * @param Query $query
     * @param string $walker
     * @return void
     */
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