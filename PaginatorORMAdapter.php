<?php

namespace Bundle\DoctrinePaginatorBundle;

use Doctrine\ORM\Query;
use Zend\Paginator\Adapter;
use DoctrineExtensions\Paginate\Paginate;

class PaginatorORMAdapter implements PaginatorAdapterInterface
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
     * @see PaginatorAdapterInterface::__construct
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
            $this->rowCount = $rowCount->getSingleScalarResult();
        } else if (is_integer($rowCount)) {
            $this->rowCount = $rowCount;
        } else {
            throw new \InvalidArgumentException("Invalid row count");
        }
    }

    /**
     * @see Zend\Paginator\Adapter:getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $ids = $this->createLimitSubquery($offset, $itemCountPerPage)
                        ->getScalarResult();

        $ids = array_map(function ($e) { return current($e); }, $ids);

        return $this->createWhereInQuery($ids)->getResult();
    }

    /**
     * @see Zend\Paginator\Adapter:count
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            $this->setRowCount($this->createCountQuery());
        }
        return $this->rowCount;
    }

    protected function createCountQuery()
    {
        return Paginate::createCountQuery($this->query);
    }

    protected function createLimitSubquery($offset, $itemCountPerPage)
    {
        return Paginate::createLimitSubQuery($this->query, $offset, $itemCountPerPage);
    }

    protected function createWhereInQuery($ids)
    {
        return Paginate::createWhereInQuery($this->query, $ids);
    }

}
