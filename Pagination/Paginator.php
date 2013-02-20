<?php

namespace Knp\Bundle\PaginatorBundle\Pagination;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Paginator as BasePaginator;

/**
 * { @inheritdoc }
 */
class Paginator extends BasePaginator
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * { @inheritdoc }
     */
    public function __construct(EventDispatcher $eventDispatcher = null, Request $request)
    {
        parent::__construct($eventDispatcher);

        $this->request = $request;
    }

    /**
     * { @inheritdoc }
     */
    public function paginate($target, $page = 1, $limit = 10, $options = array())
    {
        $limit = $this->getLimitValue($limit);
        $page  = $this->getPageValue($page);

        return parent::paginate($target, $page, $limit, $options);
    }

    /**
     * Get the page number.
     *
     * @param int $default
     *
     * @return int
     */
    public function getPageValue($default = 1)
    {
        if (null === ($page = $this->request->get($this->defaultOptions['pageParameterName']))) {
            $page = $default;
        }

        return intval(abs($page));
    }

    /**
     * Get the number of items per page
     *
     * @param null $default
     *
     * @return int
     */
    public function getLimitValue($default = null)
    {
        if (null === ($limit = $this->request->get($this->defaultOptions['limitParameterName']))) {
            if ($default !== null) {
                $limit = $default;
            } else {
                $limit = $this->defaultOptions['limitValue'];
            }
        }

        return intval(abs($limit));
    }
}
