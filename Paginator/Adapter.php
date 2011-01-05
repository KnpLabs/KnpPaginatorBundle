<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator;

use Zend\Paginator\Adapter as ZendPaginatorAdapter;

/**
 * Doctrine Paginator Adapter interface
 */
interface Adapter extends ZendPaginatorAdapter
{
    /**
     * Set the query object for the adapter
     * to be paginated
     * 
     * @param Query $query - The query to paginate
     * @param integer $numRows(optional) - number of rows
     */
    public function setQuery($query, $numRows = null);
    
	/**
     * Set the distinct mode
     * 
     * @param bool $distinct
     */
    public function setDistinct($distinct);
}