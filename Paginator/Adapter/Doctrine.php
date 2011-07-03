<?php

namespace Knp\Bundle\PaginatorBundle\Paginator\Adapter;

use Knp\Bundle\PaginatorBundle\Paginator\Adapter,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Knp\Bundle\PaginatorBundle\Event\CountEvent,
    Knp\Bundle\PaginatorBundle\Event\ItemsEvent,
    Knp\Bundle\PaginatorBundle\Exception\InvalidArgumentException,
    Knp\Bundle\PaginatorBundle\Exception\RuntimeException,
    Knp\Bundle\PaginatorBundle\Exception\UnexpectedValueException;

/**
 * Doctrine Paginator Adapter.
 * Customized for the event based extendability.
 */
class Doctrine implements Adapter
{
    /**
     * ORM query class
     */
    const QUERY_CLASS_ORM = 'Doctrine\ORM\Query';

    /**
     * ODM query class
     */
    const QUERY_CLASS_ODM = 'Doctrine\ODM\MongoDB\Query\Query';

    /**
     * List of listener services type => serviceIds
     * types supported:
     *      orm - doctrine orm
     *      odm - ducument manager
     *
	 * @var array
     */
    protected $listenerServices = array();

    /**
     * Currently used type
     *
     * @var string
     */
    protected $usedType = null;

    /**
     * Query object for pagination query
     *
     * @var object - ORM or ODM query object
     */
    protected $query = null;

    /**
     * EventDispacher
     *
     * @var Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $eventDispatcher = null;

    /**
     * True to paginate in distinct mode
     *
     * @var boolean
     */
    protected $distinct = true;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $rowCount = null;

    /**
     * Container used for tagged event loading.
     * Strictly private usage.
     *
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container = null;

    /**
     * Used alias for the paginator to support
     * multiple paginators in one request
     *
     * @var string
     */
    private $alias = '';

    /**
     * Initialize the doctrine paginator adapter
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Set the distinct mode
     *
     * @param bool $distinct
     * @return Knp\Bundle\PaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setDistinct($distinct)
    {
        $this->distinct = (bool)$distinct;

        return $this;
    }

    /**
     * Set the alias for this paginator, all
     * request parameters will be aliased by it
     *
     * @param string $alias
     * @return Knp\Bundle\PaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set the query object for the adapter
     * to be paginated.
     *
     * @param Query $query - The query to paginate
     * @param integer $numRows(optional) - number of rows
     * @throws InvalidArgumentException - if query type is not supported
     * @return Knp\Bundle\PaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setQuery($query, $numRows = null)
    {
        $type = null;
        switch (get_class($query)) {
            case self::QUERY_CLASS_ORM:
                $type = 'orm';
                break;

            case self::QUERY_CLASS_ODM:
                $type = 'odm';
                break;

            default:
                throw new InvalidArgumentException("The query supplied must be ORM or ODM Query object, [" . get_class($query) . "] given");
        }

        if ($this->usedType != $type) {
            $this->eventDispatcher = new EventDispatcher();
            foreach ($this->listenerServices[$type] as $options) {
                $this->eventDispatcher->addSubscriber($this->container->get($options['service']), $options['priority']);
            }
            $this->usedType = $type;
        }
        $this->query = $query;
        $this->rowCount = is_null($numRows) ? null : intval($numRows);

        return $this;
    }

    /**
     * Executes count on supplied query
     *
     * @throws UnexpectedValueException - if event is not finally processed or query not set
     * @return integer
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            if ($this->query === null) {
                throw new UnexpectedValueException('Paginator Query must be supplied at this point');
            }

            $event = new CountEvent($this->query, $this->distinct, $this->getAlias());
            $this->eventDispatcher->dispatch(CountEvent::NAME, $event);
            if (!$event->isPropagationStopped()) {
                throw new RuntimeException('Some listener must process an event during the "count" method call');
            }
            $this->rowCount = $event->getCount();
        }

        return $this->rowCount;
    }

    /**
     * Executes the pagination query
     *
     * @param integer $offset
     * @param integer $itemCountPerPage
     * @throws UnexpectedValueException - if event is not finally processed or query not set
     * @return mixed - resultset
     */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->query === null) {
            throw new UnexpectedValueException('Paginator Query must be supplied at this point');
        }

        $event = new ItemsEvent($this->query, $this->distinct, $offset, $itemCountPerPage, $this->getAlias());
        $this->eventDispatcher->dispatch(ItemsEvent::NAME, $event);
        if (!$event->isPropagationStopped()) {
             throw new RuntimeException('Some listener must process an event during the "getItems" method call');
        }

        return $event->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function addListenerService($serviceId, $type, $priority)
    {
        $this->listenerServices[$type][] = array('service' => $serviceId, 'priority' => $priority);
    }

    /**
     * Clone the adapter. Resets rowcount and query
     */
    public function __clone()
    {
        $this->rowCount = null;
        $this->query = null;
    }
}
