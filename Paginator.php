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

    /**
     * @see ZendPaginator::_loadScrollingStyle 
     * Uses autoload to load the scrolling style class 
     */
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        if ($scrollingStyle === null) {
            $scrollingStyle = self::$_defaultScrollingStyle;
        }

        if(is_string($scrollingStyle)) {
            class_exists('Zend\\Paginator\\ScrollingStyle\\'.$scrollingStyle);
        }

        return parent::_loadScrollingStyle($scrollingStyle);
    }
}
