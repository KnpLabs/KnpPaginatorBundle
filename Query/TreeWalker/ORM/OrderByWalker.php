<?php
/**
 * DoctrineExtensions Paginate
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Bundle\DoctrinePaginatorBundle\Query\TreeWalker\ORM;

use Doctrine\ORM\Query\TreeWalkerAdapter,
    Doctrine\ORM\Query\AST\SelectStatement,
    Doctrine\ORM\Query\AST\SelectExpression,
    Doctrine\ORM\Query\AST\PathExpression,
    Doctrine\ORM\Query\AST\AggregateExpression;

class OrderByWalker extends TreeWalkerAdapter
{
    const HINT_PAGINATOR_SORT_ALIAS = 'bundle.doctrine_paginator.sort.alias';
    const HINT_PAGINATOR_SORT_FIELD = 'bundle.doctrine_paginator.sort.field';
    const HINT_PAGINATOR_SORT_DIRECTION = 'bundle.doctrine_paginator.sort.direction';
    
    /**
     * Walks down a SelectStatement AST node, modifying it to retrieve a COUNT
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
            throw new \RuntimeException('invalid sort key alias');
        }
        $meta = $components[$alias];
        if (!$meta->hasField($field)) {
            throw new \RuntimeException('invalid sort key field');
        }

        $direction = $query->getHint(self::HINT_PAGINATOR_SORT_DIRECTION);
        /*$pathExpression = new PathExpression(
            PathExpression::TYPE_STATE_FIELD | PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION, $parentName,
            $parent['metadata']->getSingleIdentifierFieldName()
        );
        $pathExpression->type = PathExpression::TYPE_STATE_FIELD;
        
        $distinct = $this->_getQuery()->getHint(self::HINT_PAGINATOR_COUNT_DISTINCT);
        $AST->selectClause->selectExpressions = array(
            new SelectExpression(
                new AggregateExpression('count', $pathExpression, $distinct), null
            )
        );*/

        // ORDER BY is not needed, only increases query execution through unnecessary sorting.
        $AST->orderByClause = null;
    }
}