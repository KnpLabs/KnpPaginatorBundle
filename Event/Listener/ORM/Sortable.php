<?php

namespace Knp\Bundle\PaginatorBundle\Event\Listener\ORM;

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Knp\Bundle\PaginatorBundle\Event\ItemsEvent,
    Knp\Bundle\PaginatorBundle\Query\Helper as QueryHelper,
    Knp\Bundle\PaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker,
    Symfony\Component\HttpFoundation\Request,
    Knp\Bundle\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * ORM Sortable listener is responsible
 * for sorting the resultset by request
 * query parameters
 */
class Sortable implements EventSubscriberInterface
{
    /**
     * AST Tree Walker for sorting operation
     */
    const TREE_WALKER_ORDER_BY = 'Knp\Bundle\PaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker';

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
     * @param ItemsEvent $event
     */
    public function items(ItemsEvent $event)
    {
        $params = $this->request->query->all();

        $sortKey = $event->getAlias().'sort';
        $directionKey = $event->getAlias().'direction';
        
        if (isset($params[$sortKey])) {
            $query = $event->getQuery();
            
            $whitelist = $query->getHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELDS_WHITELIST);

            if($whitelist !== null && isset($whitelist[$params[$sortKey]])) {
                $sortField = $whitelist[$params[$sortKey]];
            } elseif($whitelist === false) {
                $sortField = $params[$sortKey];
            } else {
                //passed field name do not match to whitelist, skip sorting
                return;
            }
            
            $parts = explode('.', $sortField);
            if (count($parts) != 2) {
                throw new UnexpectedValueException('Invalid sort key came by request, should be example: "article.title"');
            }

            $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS, current($parts))
                ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION, (stripos($params[$directionKey], 'desc') === false) ? 'ASC' : 'DESC')
                ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELD, end($parts));
            QueryHelper::addCustomTreeWalker($query, self::TREE_WALKER_ORDER_BY);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ItemsEvent::NAME => 'items'
        );
    }
}
