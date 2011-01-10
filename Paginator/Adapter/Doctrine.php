<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator\Adapter;

use Bundle\DoctrinePaginatorBundle\Paginator\Adapter,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Bundle\DoctrinePaginatorBundle\Event\PaginatorEvent,
    Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener,
    Bundle\DoctrinePaginatorBundle\Paginator\AdapterException;

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
     * Currently used event service tag
     * 
     * @var string
     */
    protected $usedEventServiceTag = null;
    
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
     * @return Bundle\DoctrinePaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setDistinct($distinct)
    {
        $this->distinct = (bool)$distinct;
        return $this;
    }
    
    /**
     * Set the query object for the adapter
     * to be paginated.
     * 
     * @param Query $query - The query to paginate
     * @param integer $numRows(optional) - number of rows
     * @throws AdapterException - if query type is not supported
     * @return Bundle\DoctrinePaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setQuery($query, $numRows = null)
    {
        $tagName = 'doctrine_paginator.listener.';
        switch (get_class($query)) {
            case self::QUERY_CLASS_ORM:
                $tagName .= 'orm';
                break;
                
            case self::QUERY_CLASS_ODM:
                $tagName .= 'odm';
                break;
                
            default:
                throw AdapterException::invalidQuery(get_class($query));
        }
        
        if ($this->usedEventServiceTag != $tagName) {
            $this->eventDispatcher = new EventDispatcher();
            foreach ($this->container->findTaggedServiceIds($tagName) as $id => $attributes) {
                $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
                $this->container->get($id)->subscribe($this->eventDispatcher, $priority);
            }
            $this->usedEventServiceTag = $tagName;
        }
        $this->query = $query;
        $this->rowCount = is_null($numRows) ? null : intval($numRows);
        return $this;
    }
    
    /**
     * Executes count on supplied query
     * 
     * @throws AdapterException - if event is not finally processed or query not set
     * @return integer
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            if ($this->query === null) {
                throw AdapterException::queryIsMissing();
            }
            
            $eventParams = array(
                'query' => $this->query,
                'distinct' => $this->distinct
            );
            $event = new PaginatorEvent($this, PaginatorListener::EVENT_COUNT, $eventParams);
            $this->eventDispatcher->notifyUntil($event);
            if (!$event->isProcessed()) {
                 throw AdapterException::eventIsNotProcessed('count');
            }
            $this->rowCount = $event->getReturnValue();
        }
        return $this->rowCount;
    }
    
	/**
	 * Executes the pagination query
	 * 
	 * @param integer $offset
	 * @param integer $itemCountPerPage
	 * @throws AdapterException - if event is not finally processed or query not set
	 * @return mixed - resultset
	 */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->query === null) {
            throw AdapterException::queryIsMissing();
        }
        $eventParams = array(
            'query' => $this->query,
            'distinct' => $this->distinct,
            'offset' => $offset,
            'numRows' => $itemCountPerPage
        );
        $event = new PaginatorEvent($this, PaginatorListener::EVENT_ITEMS, $eventParams);
        $this->eventDispatcher->notifyUntil($event);
        if (!$event->isProcessed()) {
             throw AdapterException::eventIsNotProcessed('getItems');
        }
        return $event->getReturnValue();
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
