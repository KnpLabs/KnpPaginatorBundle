<?php

namespace Knplabs\PaginatorBundle\Query\TreeWalker\Sortable;

use Doctrine\ORM\Query\TreeWalkerAdapter,
    Doctrine\ORM\Query\AST\SelectStatement,
    Doctrine\ORM\Query\AST\PathExpression,
    Doctrine\ORM\Query\AST\OrderByItem,
    Doctrine\ORM\Query\AST\OrderByClause,
    Knplabs\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * OrderBy Query TreeWalker for Sortable functionality
 * in doctrine paginator
 */
class OrderByWalker extends TreeWalkerAdapter
{
    /**
     * Sort key alias hint name
     */
    const HINT_PAGINATOR_SORT_ALIAS = 'bundle.knplabs_paginator.sort.alias';
    
    /**
     * Sort key field hint name
     */
    const HINT_PAGINATOR_SORT_FIELD = 'bundle.knplabs_paginator.sort.field';
    
    /**
     * Sort direction hint name
     */
    const HINT_PAGINATOR_SORT_DIRECTION = 'bundle.knplabs_paginator.sort.direction';
    
    /**
     * Walks down a SelectStatement AST node, modifying it to
     * sort the query like requested by url
     *
     * @param SelectStatement $AST
     * @return void
     */
    public function walkSelectStatement(SelectStatement $AST)
    {
        $query = $this->_getQuery();
        $field = $query->getHint(self::HINT_PAGINATOR_SORT_FIELD);
        $alias = $query->getHint(self::HINT_PAGINATOR_SORT_ALIAS);
        
        $components = $this->_getQueryComponents();
        if (!array_key_exists($alias, $components)) {
            throw new UnexpectedValueException("There is no component aliased by [{$alias}] in the given Query");
        }
        $meta = $components[$alias];
        if (!$meta['metadata']->hasField($field)) {
            throw new UnexpectedValueException("There is no such field [{$field}] in the given Query component, aliased by [$alias]");
        }

        $direction = $query->getHint(self::HINT_PAGINATOR_SORT_DIRECTION);
        $pathExpression = new PathExpression(PathExpression::TYPE_STATE_FIELD, $alias, $field);
        $pathExpression->type = PathExpression::TYPE_STATE_FIELD;
        
        $orderByItem = new OrderByItem($pathExpression);
        $orderByItem->type = $direction;
        
        if ($AST->orderByClause) {
            array_unshift($AST->orderByClause->orderByItems, $orderByItem);
        } else {
            $AST->orderByClause = new OrderByClause(array($orderByItem));
        }
    }
}
