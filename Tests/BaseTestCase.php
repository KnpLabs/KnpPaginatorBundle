<?php

namespace Knplabs\PaginatorBundle\Tests;

use Symfony\Bundle\AsseticBundle\DependencyInjection\AsseticExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Scope;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;


/**
 * The general ideas on mock implementation is used
 * from AsseticBundle tests
 */
class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $requestHeaders;
    protected $container;
    protected $kernel;
    
    static public function assertSaneContainer(Container $container, $message = 'Some of container services are invalid')
    {
        $errors = array();
        foreach ($container->getServiceIds() as $id) {
            try {
                $container->get($id);
            } catch (\Exception $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        self::assertEquals(array(), $errors, $message);
    }
    
    protected function getContainerBuilder()
    {
        // \sys_get_temp_dir()
        $container = new ContainerBuilder();
        $container->addScope(new Scope('request'));
        $container->register('request', 'Symfony\\Component\\HttpFoundation\\Request')->setScope('request');
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.root_dir', __DIR__);
        $container->setParameter('kernel.cache_dir', __DIR__);
        $container->setParameter('kernel.bundles', array());
        return $container;
    }
    
    protected function getBaseKernelMock()
    {
        return $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Kernel')
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    protected function getDumpedContainer()
    {
        static $i = 0;
        $class = 'PaginatorExtensionTestContainer'.$i++;

        $this->container->compile();

        $dumper = new PhpDumper($this->container);
        eval('?>'.$dumper->dump(array('class' => $class)));

        $container = new $class();
        $container->enterScope('request');
        $container->set('kernel', $this->kernel);

        return $container;
    }
    
    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     * 
     * @return EntityManager
     */
    protected function getMockSqliteEntityManager()
    {
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $config = $this->getMock('Doctrine\ORM\Configuration');
        $config->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(\sys_get_temp_dir()));

        $config->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'));
            
        $config->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true));

        $config->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'));
            
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
        $mappingDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader);
            
        $config->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver));

        $evm = $this->getMock('Doctrine\Common\EventManager');
        $em = \Doctrine\ORM\EntityManager::create($conn, $config, $evm);
        return $em;
    }
}