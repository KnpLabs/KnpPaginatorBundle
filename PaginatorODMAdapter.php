<?php

namespace Bundle\DoctrinePaginatorBundle;

use Zend\Paginator\Adapter;
use Doctrine\ODM\MongoDB\Query;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Doctrine\ODM\MongoDB\Query objects and DQL strings
 */
class PaginatorODMAdapter implements Adapter
{
    /**
     * The query to paginate
     *
     * @var Query
     */
    protected $query = null;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $rowCount = null;

    /**
     * Constructor
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Sets the total row count for this paginator
     *
     * Can be either an integer, or a Query object which returns the count
     *
     * @param Query|integer $rowCount
     * @return void
     */
    public function setRowCount($rowCount)
    {
        if ($rowCount instanceof Query) {
            $this->rowCount = $rowCount->count();
        } else if (is_integer($rowCount)) {
            $this->rowCount = $rowCount;
        } else {
            throw new \InvalidArgumentException("Invalid row count");
        }
    }

    /**
     * Returns an collection of items for a page.
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $query = clone $this->query;

        return $query->skip($offset)->limit($itemCountPerPage)->execute()->getResults();
    }

    /**
     * @param Query $query
     * @return int
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            $this->setRowCount($this->query->count());
        }

        return $this->rowCount;
    }
}
