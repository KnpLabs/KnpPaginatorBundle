<?php

namespace Knplabs\PaginatorBundle\Tests\Paginator\Adapter;

use Knplabs\PaginatorBundle\Tests\BaseTestCase;
use Knplabs\PaginatorBundle\DependencyInjection\KnplabsPaginatorExtension;
use Knplabs\PaginatorBundle\DependencyInjection\Compiler\PaginatorConfigurationPass;
use Knplabs\PaginatorBundle\Tests\Fixture\Entity\Article;
use Knplabs\PaginatorBundle\Tests\Fixture\Entity\Comment;

class DoctrineORMTest extends BaseTestCase
{
    const FIXTURE_ARTICLE = 'Knplabs\\PaginatorBundle\\Tests\\Fixture\\Entity\\Article';
    const FIXTURE_COMMENT = 'Knplabs\\PaginatorBundle\\Tests\\Fixture\\Entity\\Comment';
    
    private $em;
    
    protected function setUp()
    {
        if (!class_exists('Zend\Paginator\Paginator')) {
            $this->markTestSkipped('Zend paginator library is required');
        }
        
        $this->kernel = $this->getBaseKernelMock();
        $this->container = $this->getContainerBuilder();
        $this->em = $this->getMockSqliteEntityManager();
        
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $schemaTool->dropSchema(array());
        $schemaTool->createSchema(array(
            $this->em->getClassMetadata(self::FIXTURE_ARTICLE),
            $this->em->getClassMetadata(self::FIXTURE_COMMENT)
        ));
        
        $this->populate();
    }
    
    public function testDoctrineAdapter()
    {
        $extension = new KnplabsPaginatorExtension();
        $this->container->registerExtension($extension);
        $this->container->addCompilerPass(new PaginatorConfigurationPass);
        $extension->load(array(array()), $this->container);

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);
        
        $adapter = $container->get('knplabs_paginator.adapter');
        $meta = $this->em->getClassMetadata(self::FIXTURE_ARTICLE);
        $query = $this->em->createQuery("SELECT a FROM {$meta->name} a ORDER BY a.title");
        $adapter->setQuery($query);
        
        $this->assertEquals(6, $adapter->count());
        $items = $adapter->getItems(2, 2); // second page, showing 2 items per page
        
        $this->assertEquals(2, count($items));
        $this->assertEquals('Sport', $items[0]->getTitle());
        $this->assertEquals('Spring', $items[1]->getTitle());
    }
    
    public function testComplicatedQuery()
    {
        $extension = new KnplabsPaginatorExtension();
        $this->container->registerExtension($extension);
        $this->container->addCompilerPass(new PaginatorConfigurationPass);
        $extension->load(array(array()), $this->container);

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);
        
        $adapter = $container->get('knplabs_paginator.adapter');
        $meta = $this->em->getClassMetadata(self::FIXTURE_ARTICLE);
        $query = $this->em->createQuery("SELECT a, c FROM {$meta->name} a LEFT JOIN a.comments c GROUP BY a.type ORDER BY a.title ASC");
        $adapter->setQuery($query);
        
        $this->assertEquals(2, $adapter->count());
        $items = $adapter->getItems(0, 2); // first page, showing 2 items per page
        
        $this->assertEquals(2, count($items));
        $this->assertEquals('Cars', $items[0]->getTitle());
        $this->assertEquals(1, $items[0]->getComments()->count());
        $this->assertEquals('Winter', $items[1]->getTitle());
        $this->assertEquals(0, $items[1]->getComments()->count());
    }
    
    private function populate()
    {
        $spring = new Article;
        $spring->setTitle('Spring');
        $spring->setType('season');
        
        $summer = new Article;
        $summer->setTitle('Summer');
        $summer->setType('season');
        
        $winter = new Article;
        $winter->setTitle('Winter');
        $winter->setType('season');
        
        $autumn = new Article;
        $autumn->setTitle('Autumn');
        $autumn->setType('season');
        
        $sport = new Article;
        $sport->setTitle('Sport');
        $sport->setType('misc');
        
        $cars = new Article;
        $cars->setTitle('Cars');
        $cars->setType('misc');
                
        $carsComment0 = new Comment;
        $carsComment0->setMessage('hi');
        $cars->addComment($carsComment0);
        
        $this->em->persist($spring);
        $this->em->persist($summer);
        $this->em->persist($autumn);
        $this->em->persist($winter);
        $this->em->persist($sport);
        $this->em->persist($cars);
        $this->em->persist($carsComment0);
        $this->em->flush();
        $this->em->clear();
    }
}