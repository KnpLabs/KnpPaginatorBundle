Provides pagination for your Doctrine ODM + Symfony2 Project.

As for now, it only works for ODM; but ORM may be also supported later.

It provides a PaginatorODMAdapter.

## Installation

### Add DoctrinePaginatorBundle to your src/Bundle dir

    git submodule add git://github.com/knplabs/DoctrinePaginatorBundle.git src/Bundle/DoctrinePaginatorBundle

### Add DoctrineUserBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\DoctrinePaginatorBundle\DoctrinePaginatorBundle(),
            // ...
        );
    }

### Add Zend Framework to the include path

ZF2 fails to load some classes properly. We need to add ZF2 path to PHP include path:

    // src/autoload.php
    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__.'/vendor/zend/library');

## Usage

### Inside controller:

    use Zend\Paginator\Paginator;
    use Bundle\DoctrinePaginatorBundle\PaginatorODMAdapter;

    $query = $documentRepository->createQuery(); // create a Doctrine ODM query
    $paginator = new Paginator(new PaginatorODMAdapter($query)); // create a paginator with the query
    $paginator->setCurrentPageNumber($this['request']->query->get('page', 1)); // set the current page number based on request parameter
    $paginator->setItemCountPerPage(10); // set max items per page
    $paginator->setPageRange(5); // set number of page links to show

    return $this->render($template, array('paginator' => $paginator));

See more exemples on [Zend Framework Paginator documentation](http://framework.zend.com/manual/en/zend.paginator.usage.html).

### Inside template:

See pagination exemples on [Zend Framework Paginator documentation](http://framework.zend.com/manual/en/zend.paginator.usage.html).
