<?php

namespace Bundle\DoctrinePaginatorBundle\Tests;

use Bundle\DoctrinePaginatorBundle\Entity\Test;
use Bundle\DoctrinePaginatorBundle\Paginator;

class PaginatorTest extends BaseDatabaseTest
{
    protected $dataCount = 45;

    public function testDataSet()
    {
        $this->assertEquals($this->dataCount, $this->createQuery()->count());
    }

    public function testCreatePaginator()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $this->assertTrue($paginator instanceof Paginator);
    }

    public function testCurrentPageNumber()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $this->assertEquals(1, $paginator->getCurrentPageNumber());

        $paginator->setCurrentPageNumber(5);
        $this->assertEquals(5, $paginator->getCurrentPageNumber());
    }

    public function testCountPages()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $this->assertEquals(5, $paginator->count());

        $paginator->setItemCountPerPage(20);
        $this->assertEquals(3, $paginator->count());
    }

    public function testCountItems()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $this->assertEquals($this->dataCount, $paginator->getTotalItemCount());
    }

    public function testCurrentItems()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $items = $paginator->getCurrentItems();
        $this->assertEquals(10, count($items));
        $this->assertEquals('test 11', reset($items)->title);
        $this->assertEquals('test 20', end($items)->title);
    }

    public function testCurrentItemsOnLastPage()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $paginator->setCurrentPageNumber(5);
        $items = $paginator->getCurrentItems();
        $this->assertEquals(5, count($items));
        $this->assertEquals('test 51', reset($items)->title);
        $this->assertEquals('test 55', end($items)->title);
    }

    public function testGetCurrentItemCount()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $this->assertEquals(10, $paginator->getCurrentItemCount());

        $paginator->setItemCountPerPage(20);
        $this->assertEquals(20, $paginator->getCurrentItemCount());
    }

    public function testGetPages()
    {
        $query = $this->createQuery();
        $paginator = new Paginator($query);
        $pages = $paginator->getPages();
        var_dump($pages);die;
    }

    protected function createQuery()
    {
        return self::createKernel()->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository('Bundle\DoctrinePaginatorBundle\Document\Test')->createQuery()->sort('title', 'asc');
    }
}
