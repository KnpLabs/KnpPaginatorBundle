<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\PaginationInterface;

interface SlidingPaginationInterface extends PaginationInterface
{
    public function getRoute(): ?string;

    public function getParams(): array;

    public function isSorted(?string $key = null, array $params = []): bool;

    public function getPaginationData(): array;

    public function getPaginatorOptions(): ?array;

    public function getCustomParameters(): ?array;
    
    public function getPage(): ?int;
    
    public function getSort(): ?string;
    
    public function getDirection(): ?string;
    
    public function getPageCount(): int;
}
