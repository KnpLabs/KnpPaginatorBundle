<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator;

use Zend\Paginator\Adapter as ZendPaginatorAdapter;

interface Adapter extends ZendPaginatorAdapter
{
    /**
     * @param Query The query to paginate
     */
    public function setQuery($query);
    
    /**
     * Sets the total row count for this paginator
     *
     * Can be either an integer, or a Query object which returns the count
     *
     * @param Query|integer $rowCount
     * @return void
     */
    public function setRowCount($numRows);
}