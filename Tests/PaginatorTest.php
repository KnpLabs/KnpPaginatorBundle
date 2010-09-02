<?php

namespace Bundle\DoctrinePaginatorBundle\Tests;

use Bundle\DoctrinePaginatorBundle\Entity\Test;
use Bundle\DoctrinePaginatorBundle\PaginatorODMAdapter;
use Zend\Paginator\Paginator;

class PaginatorTest extends BaseDatabaseTest
{
    protected $dataCount = 45;
    protected static $kernel;
    protected $isOdmAvailable;

    public function setUp()
    {
        if(null === $this->isOdmAvailable) {
            $this->isOdmAvailable = self::$kernel->getContainer()->has('doctrine.odm.mongodb.document_manager');
        }

        if (!$this->isOdmAvailable) {
            $this->markTestSkipped('Doctrine MongoDB ODM is not available.');
        }
    }

    public function testDataSet()
    {
        $this->assertEquals($this->dataCount, $this->createQuery()->count());
    }

    public function testCreatePaginator()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $this->assertTrue($paginator instanceof Paginator);
    }

    public function testCurrentPageNumber()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $this->assertEquals(1, $paginator->getCurrentPageNumber());

        $paginator->setCurrentPageNumber(5);
        $this->assertEquals(5, $paginator->getCurrentPageNumber());
    }

    public function testCountPages()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $this->assertEquals(5, $paginator->count());

        $paginator->setItemCountPerPage(20);
        $this->assertEquals(3, $paginator->count());
    }

    public function testCountItems()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $this->assertEquals($this->dataCount, $paginator->getTotalItemCount());
    }

    public function testCurrentItems()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $items = $paginator->getCurrentItems();
        $this->assertEquals(10, count($items));
        $this->assertEquals('test 11', reset($items)->title);
        $this->assertEquals('test 20', end($items)->title);
    }

    public function testCurrentItemsOnLastPage()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $paginator->setCurrentPageNumber(5);
        $items = $paginator->getCurrentItems();
        $this->assertEquals(5, count($items));
        $this->assertEquals('test 51', reset($items)->title);
        $this->assertEquals('test 55', end($items)->title);
    }

    public function testGetCurrentItemCount()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $this->assertEquals(10, $paginator->getCurrentItemCount());

        $paginator->setItemCountPerPage(20);
        $this->assertEquals(20, $paginator->getCurrentItemCount());
    }

    public function testGetPages()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $pages = $paginator->getPages();
        $this->assertTrue(is_object($pages));
        $this->assertEquals(1, $pages->first);
        $this->assertEquals(5, $pages->last);
        $this->assertEquals(1, $pages->firstPageInRange);
        $this->assertEquals(5, $pages->lastPageInRange);
    }

    public function testRenderPaginationControlPage1()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $rendered = '<div class="paginationControl">1 - 10 of 45 <span class="disabled">First</span> | <span class="disabled">&lt; Previous</span> | <a href="2">  Next &gt; </a> | <a href="5">  Last </a></div>';
        $this->assertEquals($rendered, $this->renderPaginationControl($paginator));
    }

    public function testRenderPaginationControlPage6()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $paginator->setPageRange(3);
        $paginator->setCurrentPageNumber(6);
        $paginator->setItemCountPerPage(3);
        $rendered = '<div class="paginationControl">16 - 18 of 45 <a href="1">  First </a> | <a href="5">  &lt; Previous </a> | <a href="7">  Next &gt; </a> | <a href="15">  Last </a></div>';
        $this->assertEquals($rendered, $this->renderPaginationControl($paginator));
    }

    protected function renderPaginationControl(Paginator $paginator)
    {
        ob_start();
        $pager = $paginator->getPages();
?> 
<?php if ($pager->pageCount): ?>
<div class="paginationControl">
<?php echo $pager->firstItemNumber; ?> - <?php echo $pager->lastItemNumber; ?> of <?php echo $pager->totalItemCount; ?>
<?php if (isset($pager->previous)): ?>
  <a href="<?php echo $pager->first; ?>">
    First
  </a> |
<?php else: ?>
  <span class="disabled">First</span> |
<?php endif; ?>
<?php if (isset($pager->previous)): ?>
  <a href="<?php echo $pager->previous; ?>">
    &lt; Previous
  </a> |
<?php else: ?>
  <span class="disabled">&lt; Previous</span> |
<?php endif; ?>
<?php if (isset($pager->next)): ?>
  <a href="<?php echo $pager->next; ?>">
    Next &gt;
  </a> |
<?php else: ?>
  <span class="disabled">Next &gt;</span> |
<?php endif; ?>
<?php if (isset($pager->next)): ?>
  <a href="<?php echo $pager->last; ?>">
    Last
  </a>
<?php else: ?>
  <span class="disabled">Last</span>
<?php endif; ?>
</div>
<?php endif; ?>
<?php
        return trim(str_replace(array("  ", "\n"), array(" ", ""), ob_get_clean()));
    }

    public function testRealCasePage1()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $paginator->setPageRange(3);
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(3);
        $pages = $paginator->getPages();
        $this->assertTrue(is_object($pages));
        $this->assertEquals(1, $pages->first);
        $this->assertEquals(15, $pages->last);
        $this->assertEquals(1, $pages->firstPageInRange);
        $this->assertEquals(3, $pages->lastPageInRange);
        $items = $paginator->getCurrentItems();
        $this->assertEquals(3, count($items));
        $this->assertEquals('test 11', reset($items)->title);
        $this->assertEquals('test 13', end($items)->title);
    }

    public function testRealCasePage3()
    {
        $query = $this->createQuery();
        $paginator = new Paginator(new PaginatorODMAdapter($query));
        $paginator->setPageRange(3);
        $paginator->setCurrentPageNumber(3);
        $paginator->setItemCountPerPage(3);
        $pages = $paginator->getPages();
        $this->assertTrue(is_object($pages));
        $this->assertEquals(1, $pages->first);
        $this->assertEquals(15, $pages->last);
        $this->assertEquals(2, $pages->firstPageInRange);
        $this->assertEquals(4, $pages->lastPageInRange);
        $items = $paginator->getCurrentItems();
        $this->assertEquals(3, count($items));
        $this->assertEquals('test 17', reset($items)->title);
        $this->assertEquals('test 19', end($items)->title);
    }

    public static function setupBeforeClass()
    {
        self::$kernel = self::createKernel();
    }

    protected function createQuery()
    {
        return self::$kernel->getContainer()->get('doctrine.odm.mongodb.document_manager')->getRepository('Bundle\DoctrinePaginatorBundle\Document\Test')->createQuery()->sort('title', 'asc');
    }
}
