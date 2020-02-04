<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface SlidingPaginationInterface extends PaginationInterface
{
    public function getRoute(): ?string;

    public function getParams(): array;

    /**
     * @param string[]|string|null $key
     */
    public function isSorted($key = null, array $params = []): bool;

    public function getPaginationData(): array;

    public function getPaginatorOptions(): ?array;

    public function getCustomParameters(): ?array;
}
