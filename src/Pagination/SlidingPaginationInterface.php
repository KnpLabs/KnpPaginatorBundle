<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface SlidingPaginationInterface extends PaginationInterface
{
    public function getRoute(): ?string;

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array;

    /**
     * @param string[]|string|null $key
     * @param array<string, mixed> $params
     */
    public function isSorted($key = null, array $params = []): bool;

    /**
     * @return array<string, mixed>
     */
    public function getPaginationData(): array;

    /**
     * @return array<string, mixed>
     */
    public function getPaginatorOptions(): ?array;

    /**
     * @return array<string, mixed>
     */
    public function getCustomParameters(): ?array;
}
