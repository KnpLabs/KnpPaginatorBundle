<?php

namespace Knplabs\PaginatorBundle\Event\Listener\ORM;

use Knplabs\PaginatorBundle\Event\Listener\PaginatorListener,
    Knplabs\PaginatorBundle\Event\PaginatorEvent,
    Knplabs\PaginatorBundle\Query\Helper as QueryHelper,
    Knplabs\PaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker,
    Symfony\Component\HttpFoundation\Request,
    Doctrine\ORM\Query,
    Knplabs\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * ORM Sortable listener is responsible
 * for sorting the resultset by request
 * query parameters
 */
class Sortable extends PaginatorListener
{
    /**
     * AST Tree Walker for sorting operation
     */
    const TREE_WALKER_ORDER_BY = 'Knplabs\PaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker';
    
    /**
     * Current request
     * 
     * @var Request
     */
    protected $request = null;
    
    /**
     * Initialize with requests
     * 
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    /**
     * Adds a sorting to the query if request
     * parameters were set for sorting
     * 
     * @param PaginatorEvent $event
     * @throws ListenerException - if query supplied is invalid
     * @return void
     */
    public function onQuerySort(PaginatorEvent $event)
    {
        $params = $this->request->query->all();

        if (isset($params['sort'])) {
            $query = $event->get('query');
            $parts = explode('.', $params['sort']);
            if (count($parts) != 2) {
                throw new UnexpectedValueException('Invalid sort key came by request, should be example: "article.title"');
            }
            
            $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS, current($parts))
                ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION, $params['direction'])
                ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELD, end($parts));
            QueryHelper::addCustomTreeWalker($query, self::TREE_WALKER_ORDER_BY);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    protected function getEvents()
    {
        return array(
            self::EVENT_ITEMS => 'onQuerySort'
        );
    }
}