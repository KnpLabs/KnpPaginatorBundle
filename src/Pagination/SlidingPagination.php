<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Knp\Component\Pager\Pagination\AbstractPagination;

final class SlidingPagination extends AbstractPagination implements SlidingPaginationInterface
{
    /** @var string|null */
    private $route;

    /** @var array<string, mixed> */
    private $params;

    /** @var int */
    private $pageRange = 5;

    /** @var int|null */
    private $pageLimit = null;

    /** @var string|null */
    private $template;

    /** @var string|null */
    private $sortableTemplate;

    /** @var string|null */
    private $filtrationTemplate;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function setUsedRoute(?string $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setSortableTemplate(string $template): void
    {
        $this->sortableTemplate = $template;
    }

    public function getSortableTemplate(): ?string
    {
        return $this->sortableTemplate;
    }

    public function setFiltrationTemplate(string $template): void
    {
        $this->filtrationTemplate = $template;
    }

    public function getFiltrationTemplate(): ?string
    {
        return $this->filtrationTemplate;
    }

    /**
     * @param mixed $value
     */
    public function setParam(string $name, $value): void
    {
        $this->params[$name] = $value;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setPageRange(int $range): void
    {
        $this->pageRange = \abs($range);
    }

    public function setPageLimit(?int $limit): void
    {
        $this->pageLimit = $limit;
    }

    /**
     * Get url query with all parameters.
     *
     * @param array<string, mixed> $additionalQueryParams
     *
     * @return array<string, mixed> - list of query parameters
     */
    public function getQuery(array $additionalQueryParams = []): array
    {
        return \array_merge($this->params, $additionalQueryParams);
    }

    /**
     * @param string[]|string|null $key
     * @param array<string, mixed> $params
     */
    public function isSorted($key = null, array $params = []): bool
    {
        $params = \array_merge($this->params, $params);

        if (null === $key) {
            return isset($params[$this->getPaginatorOption('sortFieldParameterName')]);
        }

        if (\is_array($key)) {
            $key = \implode('+', $key);
        }

        return isset($params[$this->getPaginatorOption('sortFieldParameterName')]) && $params[$this->getPaginatorOption('sortFieldParameterName')] === $key;
    }

    public function getPage(): ?int
    {
        return $this->params[$this->getPaginatorOption('pageParameterName')] ?? null;
    }

    public function getSort(): ?string
    {
        return $this->params[$this->getPaginatorOption('sortFieldParameterName')] ?? null;
    }

    public function getDirection(): ?string
    {
        return $this->params[$this->getPaginatorOption('sortDirectionParameterName')] ?? null;
    }

    public function getPaginationData(): array
    {
        $pageCount = $this->getPageCount();
        $current = $this->currentPageNumber;

        if ($pageCount < $current) {
            $this->currentPageNumber = $current = $pageCount;
        }

        if ($this->pageRange > $pageCount) {
            $this->pageRange = $pageCount;
        }

        $delta = \ceil($this->pageRange / 2);

        if ($current - $delta > $pageCount - $this->pageRange) {
            $pages = \range($pageCount - $this->pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = \range($offset + 1, $offset + $this->pageRange);
        }

        $proximity = \floor($this->pageRange / 2);

        $startPage = $current - $proximity;
        $endPage = $current + $proximity;

        if ($startPage < 1) {
            $endPage = \min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = \max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $viewData = [
            'last' => $pageCount,
            'current' => $current,
            'numItemsPerPage' => $this->numItemsPerPage,
            'first' => 1,
            'pageCount' => $pageCount,
            'totalCount' => $this->totalCount,
            'pageRange' => $this->pageRange,
            'startPage' => $startPage,
            'endPage' => $endPage,
        ];

        if ($current > 1) {
            $viewData['previous'] = $current - 1;
        }

        if ($current < $pageCount) {
            $viewData['next'] = $current + 1;
        }

        $viewData['pagesInRange'] = $pages;
        $viewData['firstPageInRange'] = \min($pages);
        $viewData['lastPageInRange'] = \max($pages);

        if (null !== $this->getItems()) {
            $viewData['currentItemCount'] = $this->count();
            $viewData['firstItemNumber'] = 0;
            $viewData['lastItemNumber'] = 0;
            if ($viewData['totalCount'] > 0) {
                $viewData['firstItemNumber'] = (($current - 1) * $this->numItemsPerPage) + 1;
                $viewData['lastItemNumber'] = $viewData['firstItemNumber'] + $viewData['currentItemCount'] - 1;
            }
        }

        return $viewData;
    }

    public function getPageCount(): int
    {
        $count = (int) \ceil($this->totalCount / $this->numItemsPerPage);

        if (null !== $this->pageLimit) {
            return \min($count, $this->pageLimit);
        }

        return $count;
    }

    public function getPaginatorOptions(): ?array
    {
        return $this->paginatorOptions;
    }

    public function getCustomParameters(): ?array
    {
        return $this->customParameters;
    }
}
