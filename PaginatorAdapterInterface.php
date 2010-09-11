<?php

namespace Bundle\DoctrinePaginatorBundle;

use Zend\Paginator\Adapter;

interface PaginatorAdapterInterface extends Adapter
{
    /**
     * @param Query The query to paginate
     */
    public function __construct($query);

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