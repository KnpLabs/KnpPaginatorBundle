<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface SlidingPaginationInterface extends PaginationInterface
{
    public function getRoute();
    public function getParams();
    public function isSorted($key = null, array $params = []);
    public function getPaginationData();
    public function getPaginatorOptions();
    public function getCustomParameters();
}
