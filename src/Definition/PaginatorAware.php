<?php

namespace Knp\Bundle\PaginatorBundle\Definition;

@\trigger_error(sprintf('The %s class is deprecated since knplabs/knp-paginator-bundle 5.x. Use %s instead.', PaginatorAware::class, AbstractPaginatorAware::class), \E_USER_DEPRECATED);

/**
 * Class PaginatorAware.
 *
 * This is a base class that can be extended if you're too lazy to implement PaginatorAwareInterface yourself.
 *
 * @deprecated since knplabs/knp-paginator-bundle 5.x
 */
abstract class PaginatorAware extends AbstractPaginatorAware
{
}
