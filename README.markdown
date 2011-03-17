# Intro to PaginatorBundle

This is a new version of Paginator Bundle which has been made reusable, extensible, 
highly customizable and simple to use Symfony2 paginating tool
based on Zend Paginator.

It is still experimental and can be changed fundamentally.

## Requirements:

- Zend library paginator package, without any view helpers.
- Doctrine ODM or ORM active bundle.

## Features:

- Single adapter for Zend paginator, which can be used as ORM or ODM query paginator, using DIC
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Extensions based on events for ODM and ORM query customizations.
- View helper for simplified pagination templates and other custom operations like sorting.

## Drawbacks

- Currently multiple paginators are not managed during one request

## Installation and configuration:

### Get the bundle

Submodule the bundle

    git submodule add git://github.com/knplabs/PaginatorBundle.git src/Knplabs/PaginatorBundle

### Yml configuration example

    knplabs_paginator: 
        templating: ~ # enables view helper and twig

### Add PaginatorBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Knplabs\PaginatorBundle\KnplabsPaginatorBundle(),
            // ...
        );
    }

## Usage examples:

### Controller

    $em = $this->get('doctrine.orm.entity_manager');
    $dql = "SELECT a FROM VendorBlogBundle:Article a";
    $query = $em->createQuery($dql);
        
    $adapter = $this->get('knplabs_paginator.adapter');
    $adapter->setQuery($query);
    $adapter->setDistinct(true);
    
    $paginator = new \Zend\Paginator\Paginator($adapter);
    $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
    $paginator->setItemCountPerPage(10);
    $paginator->setPageRange(5);

    // if second paginator is required on same view:
    
    $adapterODM = clone $adapter;
    $adapterODM->setQuery($someODMquery);
    $paginator2 = new Paginator($adapterODM);
    ....

### View

    <table>
    <tr>
    {# sorting of properties based on query components #}
        <th>{{ paginator_sort('Id', 'a.id')|raw }}</th>
        <th>{{ paginator_sort('Title', 'a.title')|raw }}</th>
    </tr>

    {# table body #}
    {% for article in paginator %}
    <tr {% if loop.index is odd %}class="color"{% endif %}>
        <td>{{ article.getId() }}</td>
        <td>{{ article.getTitle() }}</td>
    </tr>
    {% endfor %}
    </table>
    {# display navigation #}
    <div id="navigation">
        {{ paginator_render(paginator)|raw }}
    </div>

As for now this is being implemented, currently it is fully functional with ORM
type queries, ODM is not tested well. Also multiple paginators are not supported in same view yet.
