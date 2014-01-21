# Intro to KnpPaginatorBundle

**SEO** friendly Symfony2 paginator to paginate everything

Generally this bundle is based on [Knp Pager component][knp_component_pager]. This
component introduces a different way for pagination handling. You can read more about the
internal logic on the given documentation link.

[![knpbundles.com](http://knpbundles.com/KnpLabs/KnpPaginatorBundle/badge-short)](http://knpbundles.com/KnpLabs/KnpPaginatorBundle)

**Note:** Keep **knp-components** in sync with this bundle. If you want to use
older version of KnpPaginatorBundle - use **v1.0** tag in the repository which is
suitable to paginate **ODM mongodb** and **ORM 2.0** queries

## Latest updates

For notes about latest changes please read [`CHANGELOG`](https://github.com/KnpLabs/KnpPaginatorBundle/blob/master/CHANGELOG.md),
for required changes in your code please read [`UPGRADE`](https://github.com/KnpLabs/KnpPaginatorBundle/blob/master/Resources/doc/upgrade.md)
chapter of documentation.

## Requirements:

- Knp pager component `>=1.1`
- KnpPaginatorBundle's master compatible with symfony (`>=2.0` versions).
- Twig`>=1.5` version is required if you use twig templating engine

## Features:

- Does not require initializing specific adapters
- Can be customized in any way needed, etc.: pagination view, event subscribers.
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Separation of concerns, paginator is responsible for generating the pagination view only,
pagination view - for representation purposes.

**Note:** using multiple paginators requires setting the **alias** in order to keep non
conflicting parameters. Also it gets quite complicated with a twig template, since hash arrays cannot use
variables as keys.

## More detailed documentation:

- Creating [custom pagination subscribers][doc_custom_pagination_subscriber]
- [Extending pagination](#) class (todo, may require some refactoring)
- [Customizing view][doc_templates] templates and arguments

## Installation and configuration:

Pretty simple with [Composer](http://packagist.org), add:

```json
{
    "require": {
        "knplabs/knp-paginator-bundle": "~2.4"
    }
}
```

If you use a `deps` file, add:

    [knp-components]
        git=http://github.com/KnpLabs/knp-components.git

    [KnpPaginatorBundle]
        git=http://github.com/KnpLabs/KnpPaginatorBundle.git
        target=bundles/Knp/Bundle/PaginatorBundle

Or if you want to clone the repos:

    # Install Knp components
    git clone git://github.com/KnpLabs/knp-components.git vendor/knp-components

    # Install knp paginator bundle
    git clone git://github.com/KnpLabs/KnpPaginatorBundle.git vendor/bundles/Knp/Bundle/PaginatorBundle


<a name="configuration"></a>

### Configuration example

You can configure default query parameter names and templates

```yaml
knp_paginator:
    page_range: 5                      # default page range used in pagination control
    default_options:
        page_name: page                # page query parameter name
        sort_field_name: sort          # sort field query parameter name
        sort_direction_name: direction # sort direction query parameter name
        distinct: true                 # ensure distinct results, useful when ORM queries are using GROUP BY statements
    template:
        pagination: KnpPaginatorBundle:Pagination:sliding.html.twig     # sliding pagination controls template
        sortable: KnpPaginatorBundle:Pagination:sortable_link.html.twig # sort link template
```

### Add the namespaces to your autoloader unless you are using Composer

```php
<?php
// File: app/autoload.php
$loader->registerNamespaces(array(
    'Knp\\Component'      => __DIR__.'/../vendor/knp-components/src',
    'Knp\\Bundle'         => __DIR__.'/../vendor/bundles',
    // ...
));
```

### Add PaginatorBundle to your application kernel

```php
// app/AppKernel.php
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

- `array`
- `Doctrine\ORM\Query`
- `Doctrine\ORM\QueryBuilder`
- `Doctrine\ODM\MongoDB\Query\Query`
- `Doctrine\ODM\MongoDB\Query\Builder`
- `Doctrine\Common\Collection\ArrayCollection` - any doctrine relation collection including
- `ModelCriteria` - Propel ORM query
- array with `Solarium_Client` and `Solarium_Query_Select` as elements

```php
// Acme\MainBundle\Controller\ArticleController.php

public function listAction()
{
    $em    = $this->get('doctrine.orm.entity_manager');
    $dql   = "SELECT a FROM AcmeMainBundle:Article a";
    $query = $em->createQuery($dql);

    $paginator  = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
        $query,
        $this->get('request')->query->get('page', 1)/*page number*/,
        10/*limit per page*/
    );

    // parameters to template
    return $this->render('AcmeMainBundle:Article:list.html.twig', array('pagination' => $pagination));
}
```

### View

```jinja
{# total items count #}
<div class="count">
    {{ pagination.getTotalItemCount }}
</div>
<table>
<tr>
{# sorting of properties based on query components #}
    <th>{{ knp_pagination_sortable(pagination, 'Id', 'a.id') }}</th>
    <th{% if pagination.isSorted('a.Title') %} class="sorted"{% endif %}>{{ knp_pagination_sortable(pagination, 'Title', 'a.title') }}</th>
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
    {{ knp_pagination_render(pagination) }}
</div>
```

### Translation in view
For translating the following text:
* ```%foo% name``` with translation key ```table_header_name```. The translation is in the domain ```messages```.
* ```{0} No author|{1} Author|[2,Inf] Authors``` with translation key ```table_header_author```. The translation is in the domain ```messages```.

translationCount and translationParameters can be combined.

```jinja
<table>
    <tr>
{# sorting of properties based on query components #}
       <th>{{ knp_pagination_sortable(pagination, 'Id'|trans({foo:'bar'},'messages'), 'a.id' )|raw }}</th>
       <th{% if pagination.isSorted('a.Title') %} class="sorted"{% endif %}>{{ knp_pagination_sortable(pagination, 'Title', 'a.title')|raw }}</th>
       <th>{{ knp_pagination_sortable(pagination, 'Author'|trans({}, 'messages'), 'a.author' )|raw }}</th>
    </tr>

<!-- Content of the table -->

</table>
```

### Dependency Injection

You can automatically inject a paginator service into another service by using the ```knp_paginator.injectable``` DIC tag.
The tag takes one optional argument ```paginator```, which is the ID of the paginator service that should be injected.
It defaults to ```knp_paginator```.

The class that receives the KnpPaginator service must implement ```Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface```.
If you're too lazy you can also just extend the ```Knp\Bundle\PaginatorBundle\Definition\PaginatorAware``` base class.

###### XML configuration example

```xml
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="my_bundle.paginator_aware.class">MyBundle\Repository\PaginatorAwareRepository</parameter>
    </parameters>

    <services>
        <service id="my_bundle.paginator_aware" class="my_bundle.paginator_aware.class">
            <tag name="knp_paginator.injectable" paginator="knp_paginator" />
        </service>
    </services>
</container>
```

[knp_component_pager]: https://github.com/KnpLabs/knp-components/blob/master/doc/pager/intro.md "Knp Pager component introduction"
[doc_custom_pagination_subscriber]: https://github.com/KnpLabs/KnpPaginatorBundle/tree/master/Resources/doc/custom_pagination_subscribers.md "Custom pagination subscribers"
[doc_templates]: https://github.com/KnpLabs/KnpPaginatorBundle/tree/master/Resources/doc/templates.md "Customizing Pagination templates"
