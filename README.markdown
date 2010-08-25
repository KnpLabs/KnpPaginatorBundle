Provides pagination for your Doctrine ODM + Symfony2 Project.

As for now, it only works for ODM; but ORM may be also supported later.

This paginator is an very thin layer on top of Zend\Paginator component.
It provides a DoctrineAdapter and allows to create a Paginator by passing a Doctrine Query to the constructor.

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

## Usage

### Inside controller:

    use Bundle\DoctrinePaginatorBundle\Paginator;

    $query = $documentRepository->createQuery(); // create a Doctrine ODM query
    $paginator = new Paginator($query); // create a paginator with the query
    $paginator->setCurrentPageNumber($this['request']->query->get('page', 1)); // set the current page number based on request parameter
    $paginator->setItemCountPerPage(10); // set max items per page
    $paginator->setPageRange(5); // set number of page links to show

    return $this->render($template, array('paginator' => $paginator));

### Inside template:

See pagination exemples on [Zend Framework Paginator documentation](http://framework.zend.com/manual/en/zend.paginator.usage.html)
