# Intro to DoctrinePaginatorBundle

This branch contains a new version of DoctrinePaginatorBundle which intends
to be a reusable, highly customizable and simple to use Symfony2 paginating tool
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

## Installation and configuration:

### Get the bundle

Submodule the bundle

    git submodule add git://github.com/l3pp4rd/DoctrinePaginatorBundle.git src/Bundle/DoctrinePaginatorBundle
    
Checkout the "event-based" branch

    cd src/Bundle/DoctrinePaginatorBundle
    git checkout event-based

### Yml configuration example

    doctrine_paginator.config:~
    
    doctrine_paginator.templating:~

### Add DoctrinePaginatorBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\DoctrinePaginatorBundle\DoctrinePaginatorBundle(),
            // ...
        );
    }

## Usage examples:

### Controller

    $em = $this->get('doctrine.orm.entity_manager');
    $dql = "SELECT a FROM BlogBundle:Article a";
    $query = $em->createQuery($dql);
        
    $adapter = $this->get('doctrine_paginator.adapter');
    $adapter->setQuery($query);
    $adapter->setDistinct(true);
    
    $paginator = new Paginator($adapter);
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
        <th>{{ _view.pagination.sort('Id', 'a.id')|raw }}</th>
        <th>{{ _view.pagination.sort('Title', 'a.title')|raw }}</th>
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
        {{ _view.pagination.render(paginator)|raw }}
    </div>

As for now this is being implemented, currently it works only for ORM yet.
