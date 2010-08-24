<?php

namespace Bundle\DoctrinePaginatorBundle;

use Zend\Paginator\Paginator as ZendPaginator;
use Doctrine\ODM\MongoDB\Query;
use Bundle\DoctrinePaginatorBundle\PaginatorAdapter;

class Paginator extends ZendPaginator
{
    public function __construct($adapter)
    {
        if($adapter instanceof Query) {
            $adapter = new PaginatorAdapter($adapter);
        }

        parent::__construct($adapter);
    }
}
