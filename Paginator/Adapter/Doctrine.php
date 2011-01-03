<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator\Adapter;

use Bundle\DoctrinePaginatorBundle\Paginator\Adapter,
    Symfony\Component\HttpFoundation\Request,
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
     * ORM strategy
     */
    const STRATEGY_ORM = 'orm';
    
    /**
     * ODM strategy
     */
    const STRATEGY_ODM = 'odm';
    
    /**
     * Strategy used for this paginator adapter.
     * Can be orm or odm currently
     * 
     * @var string
     */
    protected $strategy = null;
    
    /**
     * Request object, for customized paginator
     * parameters. Available for the Event
     * 
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request = null;
    
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
     * Container used for strategy loading.
     * 
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container = null;
    
	/**
	 * Initialize the doctrine paginator adapter
	 * 
	 * @param ContainerInterface $container
	 * @param Request $request - http request
	 * @param string $strategy - initial strategy
	 */
    public function __construct(ContainerInterface $container, Request $request, $strategy)
    {
        $this->request = $request;
        $this->container = $container;
        $this->strategy = $strategy;
        $this->setStrategy($strategy);
    }
    
    /**
     * Switch the strategy
     * 
     * @param string $strategy
     * @return Bundle\DoctrinePaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        $this->loadStrategy();
        return $this;
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
     * {@inheritDoc}
     * @return Bundle\DoctrinePaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @return Bundle\DoctrinePaginatorBundle\Paginator\Adapter\Doctrine
     */
    public function setRowCount($numRows)
    {
        $this->rowCount = $numRows;
        return $this;
    }
    
    /**
     * Executes count on supplied query
     * 
     * @throws AdapterException - if event is not finally processed
     * @return integer
     */
    public function count()
    {
        if (is_null($this->rowCount)) {
            if ($this->query === null) {
                AdapterException::queryIsMissing();
            }
            
            $eventParams = array(
                'query' => $this->query,
                'distinct' => $this->distinct
            );
            $event = new PaginatorEvent($this, PaginatorListener::EVENT_COUNT, $eventParams);
            $this->eventDispatcher->notifyUntil($event);
            if (!$event->isProcessed()) {
                 AdapterException::eventIsNotProcessed('count');
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
	 * @throws AdapterException - if event is not finally processed
	 * @return mixed - resultset
	 */
    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->query === null) {
            AdapterException::queryIsMissing();
        }
        $eventParams = array(
            'request' => $this->request,
            'query' => $this->query,
            'distinct' => $this->distinct,
            'offset' => $offset,
            'count' => $itemCountPerPage
        );
        $event = new PaginatorEvent($this, PaginatorListener::EVENT_ITEMS, $eventParams);
        $this->eventDispatcher->notifyUntil($event);
        if (!$event->isProcessed()) {
             AdapterException::eventIsNotProcessed('getItems');
        }
        return $event->getReturnValue();
    }
    
	/**
     * Loads the listeners depending on strategy used
     * 
     * @return void
     */
    private function loadStrategy()
    {
        $this->eventDispatcher = new EventDispatcher();
        $tagName = 'doctrine_paginator.listener.' . $this->strategy;
        foreach ($this->container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $this->container->get($id)->subscribe($this->eventDispatcher, $priority);
        }
        $this->query = null;
        $this->rowCount = null;
    }
}
