<?php

namespace Bundle\DoctrinePaginatorBundle\Tests\ORM;

use Bundle\DoctrinePaginatorBundle\Entity\Article;

class PaginatorTest extends BaseDatabaseTest
{
    protected static $kernel;

    public function setUp()
    {
        if (!self::$kernel->getContainer()->has('doctrine.orm.entity_manager')) {
            $this->markTestSkipped('Doctrine ORM is not available.');
        }
    }

    public static function setupBeforeClass()
    {
        self::$kernel = self::createKernel();
    }
}
