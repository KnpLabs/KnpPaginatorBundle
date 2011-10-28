<?php

namespace Knp\Bundle\PaginatorBundle\Tests\Paginator\Adapter;

use Knp\Bundle\PaginatorBundle\Query\TreeWalker\Sortable\OrderByWalker;

use Knp\Bundle\PaginatorBundle\Tests\BaseTestCase;
use Knp\Bundle\PaginatorBundle\DependencyInjection\KnpPaginatorExtension;
use Knp\Bundle\PaginatorBundle\DependencyInjection\Compiler\PaginatorConfigurationPass;
use Knp\Bundle\PaginatorBundle\Tests\Fixture\Entity\Article;
use Knp\Bundle\PaginatorBundle\Tests\Fixture\Entity\Comment;

class DoctrineORMTest extends BaseTestCase
{
    const FIXTURE_ARTICLE = 'Knp\\Bundle\\PaginatorBundle\\Tests\\Fixture\\Entity\\Article';
    const FIXTURE_COMMENT = 'Knp\\Bundle\\PaginatorBundle\\Tests\\Fixture\\Entity\\Comment';

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

    public function testSingleWhereStatement()
    {
        $extension = $this->createExtension();

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);

        $adapter = $container->get('knp_paginator.adapter');
        $meta = $this->em->getClassMetadata(self::FIXTURE_ARTICLE);
        $query = $this->em->createQuery("SELECT a FROM {$meta->name} a WHERE a.type = 'season' ORDER BY a.title");
        $adapter->setQuery($query);

        $this->assertEquals(4, $adapter->count());
        $items = $adapter->getItems(2, 2); // second page, showing 2 items per page

        $this->assertEquals(2, count($items));
        $this->assertEquals('Summer', $items[0]->getTitle());
        $this->assertEquals('Winter', $items[1]->getTitle());
    }
    
    private function createExtension()
    {
        $extension = new KnpPaginatorExtension();
        $this->container->registerExtension($extension);
        $this->container->addCompilerPass(new PaginatorConfigurationPass);
        $extension->load(array(array()), $this->container);
        
        return $extension;
    }

    public function testGithubIssue15()
    {
        $repo = $this->em->getRepository(self::FIXTURE_ARTICLE);
        $qb = $repo->createQueryBuilder('a');
        $qb ->select('a');
        $qb->add('where', $qb->expr()->in('a.id', array(1, 2, 3)));
        $qb->andWhere("a.type = 'season'");
        $qb->orderBy('a.title', 'desc');
        $query = $qb->getQuery();

        $extension = $this->createExtension();

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);

        $adapter = $container->get('knp_paginator.adapter');
        $adapter->setQuery($query);

        $this->assertEquals(3, $adapter->count());
        $items = $adapter->getItems(1, 2); // first page, showing 2 items per page

        $this->assertEquals(2, count($items));
        $this->assertEquals('Spring', $items[0]->getTitle());
        $this->assertEquals('Autumn', $items[1]->getTitle());

        // single where statement
        $repo = $this->em->getRepository(self::FIXTURE_ARTICLE);
        $qb = $repo->createQueryBuilder('a');
        $qb ->select('a');
        $qb->add('where', $qb->expr()->in('a.id', array(1, 2, 3)));
        $qb->orderBy('a.title', 'desc');
        $query = $qb->getQuery();
    }

    public function testDoctrineAdapter()
    {
        $extension = $this->createExtension();

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);

        $adapter = $container->get('knp_paginator.adapter');
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
        $extension = $this->createExtension();

        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);

        $adapter = $container->get('knp_paginator.adapter');
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
    
    /**
     * @dataProvider orderDirectionProvider
     */
    public function testOrderByClause($direction)
    {
        $extension = $this->createExtension();
        
        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);
        
        $adapter = $container->get('knp_paginator.adapter');
        $meta = $this->em->getClassMetadata(self::FIXTURE_ARTICLE);
        $query = $this->em->createQuery("SELECT a FROM {$meta->name} a");
        $adapter->setQuery($query);
        
        $request = $container->get('request');
        $request->query->set('sort', 'a.title');
        $request->query->set('direction', $direction);
        
        $articles = $adapter->getItems(0, 100);
        
        $actualOrder = array();
        
        foreach($articles as $article) {
            $actualOrder[] = $article->getTitle();
        }
        
        $expectedOrder = $actualOrder;
        
        if($direction === 'desc') {
            rsort($expectedOrder);
        } elseif ($direction === 'asc') {
            sort($expectedOrder);
        }
        
        $this->assertTrue($expectedOrder === $actualOrder);
    }
    
    public function orderDirectionProvider()
    {
        return array(
            array('desc'),
            array('asc'),
            array('invalid'),
        );
    }
    
    /**
     * @dataProvider orderFieldWhitelistProvider
     */
    public function testOrderFieldsWhitelist($orderField, $fieldGetter, $whitelist)
    {
        $extension = $this->createExtension();
        
        $container = $this->getDumpedContainer();
        BaseTestCase::assertSaneContainer($container);
        
        $adapter = $container->get('knp_paginator.adapter');
        $meta = $this->em->getClassMetadata(self::FIXTURE_ARTICLE);
        $query = $this->em->createQuery("SELECT a FROM {$meta->name} a");
        
        if($whitelist) {
            $query->setHint(OrderByWalker::HINT_PAGINATOR_SORT_FIELDS_WHITELIST, $whitelist);
        }

        $adapter->setQuery($query);
        
        $request = $container->get('request');
        $request->query->set('sort', $orderField);
        $request->query->set('direction', 'desc');
        
        $articles = $adapter->getItems(0, 100);
        
        if($fieldGetter !== false) {
            $actualOrder = array();
    
            foreach($articles as $article) {
                $actualOrder[] = $article->$fieldGetter();
            }
            
            $expectedOrder = $actualOrder;
            
            rsort($expectedOrder);
            
            $this->assertTrue($expectedOrder === $actualOrder);
        } else {
            //ok, exception has not been thrown in order to unknown dql query part
        }
    }
    
    public function orderFieldWhitelistProvider()
    {
        return array(
            array('a.title', 'getTitle', null),//by default for backward compatibility all fields are allowed
            array('a.unexistedField', false, array('title' => 'a.title')),
            array('customSortKey', 'getTitle', array('customSortKey' => 'a.title')),
        );
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
