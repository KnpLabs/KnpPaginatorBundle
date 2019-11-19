<?php

namespace Knp\Bundle\PaginatorBundle\Definition;

use Knp\Component\Pager\Paginator;

/**
 * Class PaginatorAware.
 *
 * This is a base class that can be extended if you're too lazy to implement PaginatorAwareInterface yourself.
 */
final class PaginatorAware implements PaginatorAwareInterface
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * Sets the KnpPaginator instance.
     */
    public function setPaginator(Paginator $paginator): PaginatorAwareInterface
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Returns the KnpPaginator instance.
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }
}
