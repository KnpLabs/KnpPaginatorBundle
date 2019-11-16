<?php

namespace Knp\Bundle\PaginatorBundle\Definition;

use Knp\Component\Pager\Paginator;

/**
 * Interface PaginatorAwareInterface.
 *
 * PaginatorAwareInterface should be implemented by classes that depend on a KnpPaginator service.
 */
interface PaginatorAwareInterface
{
    /**
     * Sets the KnpPaginator instance.
     */
    public function setPaginator(Paginator $paginator): self;
}
