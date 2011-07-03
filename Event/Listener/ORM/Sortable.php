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

        if (isset($params[$event->getAlias().'sort'])) {
            $query = $event->getQuery();
            $parts = explode('.', $params[$event->getAlias().'sort']);
            if (count($parts) != 2) {
                throw new UnexpectedValueException('Invalid sort key came by request, should be example: "article.title"');
            }

            $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_ALIAS, current($parts))
                ->setHint(OrderByWalker::HINT_PAGINATOR_SORT_DIRECTION, $params[$event->getAlias().'direction'])
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
