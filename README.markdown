# DEPRECATED - Use [ZendPaginatorAdapter](http://github.com/ornicar/ZendPaginatorAdapter) instead
ZendPaginatorAdapter is not tied to Symfony2 and is more up to date.

Provides pagination for your Doctrine ODM + Symfony2 Project.

This branch is still experimental. Intension is to extend and simplify the
usage of paginator tool for Symfony2.

Intended feature list:

- Single adapter for Zend paginator, which could be used as ORM or ODM query paginator, using DIC
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Extensions based on events for ODM and ORM query customizations.
- View helper for simplified pagination templates and other custom operations like sorting.

Some code snippets on future usage:

    $em = $this->get('doctrine.orm.entity_manager');
    $dql = "SELECT a FROM BlogBundle:Article a";
    $query = $em->createQuery($dql);
        
    $adapter = $this->get('doctrine_paginator.adapter');
    $adapter->setQuery($query);
    $paginator = new Paginator($adapter);
    $paginator->setCurrentPageNumber($this->get('request')->query->get('page', 1));
    $paginator->setItemCountPerPage(2);
    $paginator->setPageRange(5);
    
    // if second paginator is required on same view:
    $adapterODM = clone $adapter;
    $adapterODM->setStrategy('odm');
    $adapterODM->setQuery($someODMquery);
    $paginator2 = new Paginator($adapterODM);

Some code snippets on view:

    {# sorting of properties based on query components #}
    <th>{{ _view.pagination.sort('Title', 'a.title')|raw }}</th>

    {# display navigation #}
    <div id="navigation">
        {{ _view.pagination.render(paginator)|raw }}
    </div>

As for now this is being implemented, currently it working only for ORM.
