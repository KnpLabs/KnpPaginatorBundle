<?php

namespace Bundle\DoctrinePaginatorBundle;

use Doctrine\ODM\MongoDB\Query;

/**
 * Implements the Zend\Paginator\Adapter Interface for use with Zend\Paginator\Paginator
 *
 * Allows pagination of Doctrine\ODM\MongoDB\Query objects and DQL strings
 */
class PaginatorODMAdapter implements PaginatorAdapterInterface
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
     * @ses PaginatorAdapterInterface::__construct
     */
    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @see PaginatorAdapterInterface::setRowCount
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
     * @see Zend\Paginator\Adapater::getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $query = clone $this->query;

        return $query->skip($offset)->limit($itemCountPerPage)->execute()->getResults();
    }

    /**
     * @see Zend\Paginator\Adapter::count
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            $this->setRowCount($this->query->count());
        }

        return $this->rowCount;
    }
}
