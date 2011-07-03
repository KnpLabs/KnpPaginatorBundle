# Intro to PaginatorBundle

This is a new version of Paginator Bundle which has been made reusable, extensible,
highly customizable and simple to use Symfony2 paginating tool
based on Zend Paginator.

## Requirements:

- Zend library paginator package, without any view helpers. In future this requirement may be dropped
- Doctrine ODM or ORM active bundle.

## Features:

- Single adapter for Zend paginator, which can be used as ORM or ODM query paginator, using DIC
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Extensions based on events for ODM and ORM query customizations.
- View helper for simplified pagination templates and other custom operations like sorting.
- Supports multiple paginators during one request

**Notice:** using multiple paginators requires setting the alias for adapter in order to keep non
conflicting parameters. Also it gets quite complicated with a twig template, since hash arrays cannot use
variables as keys.

## Installation and configuration:

### Install Zend Framework 2

    git submodule add git://github.com/zendframework/zf2.git vendor/Zend

### Get the bundle

Submodule the bundle

    git submodule add git://github.com/knplabs/KnpPaginatorBundle.git vendor/bundles/Knp/Bundle/PaginatorBundle

### Yml configuration example

    knp_paginator:
        templating: ~ # enables view helper and twig

### Add the namespaces to your autoloader

    // app/autoload.php
    $loader->registerNamespaces(array(
        'Knp'                       => __DIR__.'/../vendor/bundles',
        'Zend'                => __DIR__.'/../vendor/Zend/library',
        // ...
    ));


### Add PaginatorBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            // ...
        );
    }

## Usage examples:

### Controller

    $em = $this->get('doctrine.orm.entity_manager');
    $dql = "SELECT a FROM VendorBlogBundle:Article a";
    $query = $em->createQuery($dql);

    $adapter = $this->get('knp_paginator.adapter');
    $adapter->setQuery($query);
    $adapter->setDistinct(true);

    $paginator = new \Zend\Paginator\Paginator($adapter);
    $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
    $paginator->setItemCountPerPage(10);
    $paginator->setPageRange(5);

    // if second paginator is required on same view:
    
    $adapterODM = $this->get('knp_paginator.adapter');
    $adapterODM->setQuery($someODMquery);
    $adapterODM->setAlias('p2_'); // we do not want parameters to conflict
    $paginator2 = new Paginator($adapterODM);
    ....

### View

    <table>
    <tr>
    {# sorting of properties based on query components #}
        <th>{{ paginator|sortable('Id', 'a.id') }}</th>
        <th>{{ paginator|sortable('Title', 'a.title') }}</th>
    </tr>

    {# table body #}
    {% for article in paginator %}
    <tr {% if loop.index is odd %}class="color"{% endif %}>
        <td>{{ article.id }}</td>
        <td>{{ article.title }}</td>
    </tr>
    {% endfor %}
    </table>
    {# display navigation #}
    <div id="navigation">
        {{ paginator|paginate }}
    </div>

