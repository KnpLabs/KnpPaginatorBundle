<?php

namespace Knplabs\Bundle\PaginatorBundle\Tests\Paginator\Adapter;

use Knplabs\Bundle\PaginatorBundle\Tests\BaseTestCase;
use Knplabs\Bundle\PaginatorBundle\DependencyInjection\KnplabsPaginatorExtension;
use Knplabs\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorConfigurationPass;
use Knplabs\Bundle\PaginatorBundle\Tests\Fixture\Document\Article;
use Knplabs\Bundle\PaginatorBundle\Tests\Fixture\Document\Comment;

class DoctrineODMTest extends BaseTestCase
{
    const FIXTURE_ARTICLE = 'Knplabs\\Bundle\\PaginatorBundle\\Tests\\Fixture\\Document\\Article';
    const FIXTURE_COMMENT = 'Knplabs\\Bundle\\PaginatorBundle\\Tests\\Fixture\\Document\\Comment';

    private $dm;

    protected function setUp()
    {
        if (!class_exists('Zend\Paginator\Paginator')) {
            $this->markTestSkipped('Zend paginator library is required');
        }

        if (!class_exists('Doctrine\ODM\MongoDB\Configuration')) {
            $this->markTestSkipped('Doctrine odm mongodb library is required');
        }

        $this->kernel = $this->getBaseKernelMock();
        $this->container = $this->getContainerBuilder();
        $this->dm = $this->getMockMongoDocumentManager();

        $this->populate();
    }

    protected function tearDown()
    {
        if ($this->dm) {
            foreach ($this->dm->getDocumentDatabases() as $db) {
                foreach ($db->listCollections() as $collection) {
                    $collection->drop();
                }
            }
            $this->dm->getConnection()->close();
            $this->dm = null;
        }
    }

    public function testSingleWhereStatement()
    {
        $extension = new KnplabsPaginatorExtension();
        $this->container->registerExtension($extension);
        $this->container->addCompilerPass(new PaginatorConfigurationPass);
        $extension->load(array(array()), $this->container);

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);

        $adapter = $container->get('knplabs_paginator.adapter');
        $meta = $this->dm->getClassMetadata(self::FIXTURE_ARTICLE);
        $qb = $this->dm->createQueryBuilder($meta->name);
        $qb->field('type')->equals('season');
        $qb->sort('title', 'ASC');

        $query = $qb->getQuery();
        $adapter->setQuery($query);

        $this->assertEquals(4, $adapter->count());
        $items = $adapter->getItems(2, 2); // second page, showing 2 items per page
        $this->assertEquals(2, count($items));
        $item = current($items);
        $this->assertEquals('Summer', $item->getTitle());
        $item = next($items);
        $this->assertEquals('Winter', $item->getTitle());
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
        $meta = $this->dm->getClassMetadata(self::FIXTURE_ARTICLE);
        $qb = $this->dm->createQueryBuilder($meta->name);
        $qb->sort('title', 'ASC');

        $query = $qb->getQuery();
        $adapter->setQuery($query);

        $this->assertEquals(6, $adapter->count());
        $items = $adapter->getItems(2, 2); // second page, showing 2 items per page
        $this->assertEquals(2, count($items));
        $item = current($items);
        $this->assertEquals('Sport', $item->getTitle());
        $item = next($items);
        $this->assertEquals('Spring', $item->getTitle());
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

        $this->dm->persist($spring);
        $this->dm->persist($summer);
        $this->dm->persist($autumn);
        $this->dm->persist($winter);
        $this->dm->persist($sport);
        $this->dm->persist($cars);
        $this->dm->persist($carsComment0);
        $this->dm->flush();
        $this->dm->clear();
    }
}