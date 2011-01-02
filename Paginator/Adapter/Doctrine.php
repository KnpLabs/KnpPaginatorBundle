<?php

namespace Bundle\DoctrinePaginatorBundle\Paginator\Adapter;

use Bundle\DoctrinePaginatorBundle\Paginator\Adapter,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event,
    Bundle\DoctrinePaginatorBundle\Event\Listener\PaginatorListener;

class Doctrine implements Adapter
{
    protected $strategy = null;
    protected $request = null;
    protected $query = null;
    protected $eventDispatcher = null;
    protected $distinct = true;
    
    private $container = null;
    
	/**
     * @param Request - http request
     */
    public function __construct(ContainerInterface $container, Request $request, $strategy)
    {
        $this->request = $request;
        $this->container = $container;
        $this->strategy = $strategy;
        $this->setStrategy($strategy);
    }
    
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        $this->loadStrategy();
    }
    
    private function loadStrategy()
    {
        $this->eventDispatcher = new EventDispatcher();
        $tagName = 'doctrine_paginator.listener.' . $this->strategy;
        foreach ($this->container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $this->container->get($id)->subscribe($this->eventDispatcher, $priority);
        }
    }
    
    public function setDistinct($distinct)
    {
        $this->distinct = (bool)$distinct;
    }
    
    public function setQuery($query)
    {
        $this->query = $query;
    }
    
    public function setRowCount($numRows)
    {
        //$this->strategy->setRowCount($numRows);
    }
    
    public function count()
    {
        $eventParams = array(
            'query' => $this->query,
            'distinct' => $this->distinct
        );
        $event = new Event($this, PaginatorListener::EVENT_COUNT, $eventParams);
        $this->eventDispatcher->notifyUntil($event);
        if (!$event->isProcessed()) {
             throw new \RuntimeException('failure');
        }
        var_dump($event->getReturnValue());
        die('i');
    }
    
	/**
     * @see Zend\Paginator\Adapter:getItems
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $eventParams = array(
            'request' => $this->request,
            'query' => $this->query,
            'distinct' => $this->distinct,
            'offset' => $offset,
            'count' => $itemCountPerPage
        );
        $event = new Event($this, PaginatorListener::EVENT_ITEMS, $eventParams);
        $this->eventDispatcher->notifyUntil($event);
        if (!$event->isProcessed()) {
             throw new \RuntimeException('failure');
        }
        return $event->getReturnValue();
    }
}
