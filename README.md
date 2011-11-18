# Intro to KnpPaginatorBundle

Generally this bundle is based on [knp Pager component][knp_component_pager]. This
component introduces a diferent way for pagination handling. You can read more about the
internal logic on the given documentation link.

**Note:** if you want to use older version of KnpPaginatorBundle - use **v1.0** tag
in the repository

## Requirements:

- Knp pager component

## Features:

- Does not require initializing specific adapters
- Can be customized in any way needed, etc.: pagination view, event subscribers.
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Separation of conserns, paginator is responsible for generating the pagination view only,
pagination view - for representation purposes.

**Notice:** using multiple paginators requires setting the **alias** in order to keep non
conflicting parameters. Also it gets quite complicated with a twig template, since hash arrays cannot use
variables as keys.

## More detailed documentation:

- [Extending paginate](#) capabilities (todo)
- [Extending pagination](#) class (todo)
- [Customizing view](#) templates and arguments (todo)

## Installation and configuration:

### Install Knp components

    git clone git://github.com/knplabs/knp-components.git vendor/knp_components

### Install knp paginator bundle

    git clone git://github.com/knplabs/KnpPaginatorBundle.git vendor/bundles/Knp/Bundle/PaginatorBundle

### Configuration example

Is it not enough symfony2 configuration? You can override default templates using parameters

``` yaml
// File: app/configs/parameters.yml

parameters:
  knp_paginator.template.pagination: MyBundle:Pagination:pagination.html.twig
  knp_paginator.template.sortable:   MyBundle:Pagination:sortable.html.twig
```

### Add the namespaces to your autoloader

``` php
<?php
// File: app/autoload.php
$loader->registerNamespaces(array(
    'Knp\\Component'      => __DIR__.'/../vendor/knp-components/src'
    'Knp\\Bundle'         => __DIR__.'/../vendor/bundles',
    // ...
));
```

### Add PaginatorBundle to your application kernel

``` php
<?php
    // File: app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            // ...
        );
    }
```

## Usage examples:

### Controller

Currently paginator can paginate:

- array
- Doctrine\ORM\Query
- Doctrine\ODM\MongoDB\Query\Query

``` php
<?php
$em = $this->get('doctrine.orm.entity_manager');
$dql = "SELECT a FROM VendorBlogBundle:Article a";
$query = $em->createQuery($dql);

$paginator = $this->get('knp_paginator');
$pagination = $paginator->paginate(
    $query,
    $this->get('request')->query->get('page', 1)/*page number*/,
    10/*limit per page*/
);

// parameters to template
return compact('pagination');
```

### View

``` html
<table>
<tr>
{# sorting of properties based on query components #}
    <th>{{ pagination.sortable('Id', 'a.id')|raw }}</th>
    <th>{{ pagination.sortable('Title', 'a.title')|raw }}</th>
</tr>

{# table body #}
{% for article in pagination %}
<tr {% if loop.index is odd %}class="color"{% endif %}>
    <td>{{ article.id }}</td>
    <td>{{ article.title }}</td>
</tr>
{% endfor %}
</table>
{# display navigation #}
<div class="navigation">
    {{ pagination.render()|raw }}
</div>
```

[knp_component_pager]: https://github.com/knplabs/knp-components/blob/master/doc/pager/intro.md "Knp Pager component introduction"
