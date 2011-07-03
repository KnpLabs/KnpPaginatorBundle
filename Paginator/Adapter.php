<?php

namespace Knp\Bundle\PaginatorBundle\Paginator;

use Zend\Paginator\Adapter as ZendPaginatorAdapter;

/**
 * Doctrine Paginator Adapter interface
 */
interface Adapter extends ZendPaginatorAdapter
{
    /**
     * Set the query object for the adapter
     * to be paginated
     *
     * @param Query $query - The query to paginate
     * @param integer $numRows(optional) - number of rows
     */
    public function setQuery($query, $numRows = null);

    /**
     * Set the distinct mode
     *
     * @param bool $distinct
     */
    public function setDistinct($distinct);

    /**
     * Get paginator alias
     *
     * @return string
     */
    public function getAlias();

    /**
     * Add a listener service by $type for
     * adaper to be able to use
     *
     * @param string $serviceId
     * @param string $type
     * @param string $priority
     */
    public function addListenerService($serviceId, $type, $priority);
}
