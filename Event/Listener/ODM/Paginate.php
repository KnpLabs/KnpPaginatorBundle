<?php

namespace Knp\Bundle\PaginatorBundle\Event\Listener\ODM;

use Knp\Bundle\PaginatorBundle\Event\CountEvent,
    Knp\Bundle\PaginatorBundle\Event\ItemsEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Doctrine\ODM\MongoDB\Query\Query,
    Knp\Bundle\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * ODM Paginate listener is responsible
 * for standard pagination of query resultset
 */
class Paginate implements EventSubscriberInterface
{
    /**
     * Executes the count on Query used for
     * pagination.
     *
     * @param CountEvent $event
     */
    public function count(CountEvent $event)
    {
        $query = $event->getQuery();
        $event->stopPropagation();
        $event->setCount($query->count());
    }

    /**
     * Generates the paginated resultset
     *
     * @param ItemsEvent $event
     */
    public function items(ItemsEvent $event)
    {
        $query = $event->getQuery();
        $type = $query->getType();
        if ($type !== Query::TYPE_FIND) {
            throw new UnexpectedValueException('ODM query must be a FIND type query');
        }
        $reflClass = new \ReflectionClass('Doctrine\MongoDB\Query\Query');
        $reflProp = $reflClass->getProperty('query');
        $reflProp->setAccessible(true);
        $queryOptions = $reflProp->getValue($query);

        $queryOptions['limit'] = $event->getRowCountPerPage();
        $queryOptions['skip'] = $event->getOffset();

        $resultQuery = clone $query;
        $reflProp->setValue($resultQuery, $queryOptions);
        $cursor = $resultQuery->execute();

        $event->stopPropagation();
        $event->setItems($cursor->toArray());
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            ItemsEvent::NAME => array('items', 0),
            CountEvent::NAME => array('count', 0)
        );
    }
}
