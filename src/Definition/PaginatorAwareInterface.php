<?php

namespace Knp\Bundle\PaginatorBundle\Definition;

use Knp\Component\Pager\PaginatorInterface;

/**
 * Interface PaginatorAwareInterface.
 *
 * PaginatorAwareInterface can be implemented by classes that depend on a KnpPaginator service.
 * You should avoid this solution: use autowiring instead.
 */
interface PaginatorAwareInterface
{
    /**
     * Sets the KnpPaginator instance.
     */
    public function setPaginator(PaginatorInterface $paginator): self;
}
