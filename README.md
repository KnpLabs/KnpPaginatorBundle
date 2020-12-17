# Intro to KnpPaginatorBundle

Friendly Symfony paginator to paginate everything

[![Build Status](https://travis-ci.org/KnpLabs/KnpPaginatorBundle.svg?branch=master)](https://travis-ci.org/KnpLabs/KnpPaginatorBundle)

Generally this bundle is based on [Knp Pager component][knp_component_pager]. This
component introduces a different way of pagination handling. You can read more about the
internal logic on the given documentation link.

[![knpbundles.com](http://knpbundles.com/KnpLabs/KnpPaginatorBundle/badge-short)](http://knpbundles.com/KnpLabs/KnpPaginatorBundle)

**Note:** Keep **knp-components** in sync with this bundle. If you want to use
older version of KnpPaginatorBundle - use **v3.0** or **v4.X** tags in the repository which is
suitable to paginate **ODM MongoDB** and **ORM 2.0** queries

## Latest updates

For notes about the latest changes please read [`CHANGELOG`](https://github.com/KnpLabs/KnpPaginatorBundle/blob/master/CHANGELOG.md),
for required changes in your code please read [`UPGRADE`](https://github.com/KnpLabs/KnpPaginatorBundle/blob/master/docs/upgrade.md)
chapter of the documentation.

## Requirements:

- Knp Pager component `>=2.0`.
- KnpPaginatorBundle's master is compatible with Symfony `>=4.4` versions.
- Twig `>=2.0` version is required if you use twig templating engine.

## Features:

- Does not require initializing specific adapters.
- Can be customized in any way needed, etc.: pagination view, event subscribers.
- Possibility to add custom filtering, sorting functionality depending on request parameters.
- Separation of concerns, paginator is responsible for generating the pagination view only,
pagination view - for representation purposes.

**Note:** using multiple paginators requires setting the **alias** in order to keep non
conflicting parameters.

## More detailed documentation:

- Creating [custom pagination subscribers][doc_custom_pagination_subscriber]
- [Extending pagination](#) class (todo, may require some refactoring)
- [Customizing view][doc_templates] templates and arguments

## Installation and configuration:

### Pretty simple with [Composer](http://packagist.org), run

```sh
composer require knplabs/knp-paginator-bundle
```

### Add PaginatorBundle to your application kernel

If you don't use flex (you should), you need to manually enable bundle:

```php
// app/AppKernel.php
public function registerBundles()
{
    return [
        // ...
        new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
        // ...
    ];
}
```

<a name="configuration"></a>

### Configuration example

You can configure default query parameter names and templates

#### YAML:
```yaml
knp_paginator:
    page_range: 5                       # number of links showed in the pagination menu (e.g: you have 10 pages, a page_range of 3, on the 5th page you'll see links to page 4, 5, 6)
    default_options:
        page_name: page                 # page query parameter name
        sort_field_name: sort           # sort field query parameter name
        sort_direction_name: direction  # sort direction query parameter name
        distinct: true                  # ensure distinct results, useful when ORM queries are using GROUP BY statements
        filter_field_name: filterField  # filter field query parameter name
        filter_value_name: filterValue  # filter value query parameter name
    template:
        pagination: '@KnpPaginator/Pagination/sliding.html.twig'     # sliding pagination controls template
        sortable: '@KnpPaginator/Pagination/sortable_link.html.twig' # sort link template
        filtration: '@KnpPaginator/Pagination/filtration.html.twig'  # filters template
```
#### PHP:
```php
// config/packages/paginator.php

<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void
{
    $configurator->extension('knp_paginator', [
        'page_range' => 5,                        // number of links showed in the pagination menu (e.g: you have 10 pages, a page_range of 3, on the 5th page you'll see links
        'default_options' => [
            'page_name' => 'page',                // page query parameter name
            'sort_field_name' => 'sort',          // sort field query parameter name
            'sort_direction_name' => 'direction', // sort direction query parameter name
            'distinct' => true,                   // ensure distinct results, useful when ORM queries are using GROUP BY statements
            'filter_field_name' => 'filterField', // filter field query parameter name
            'filter_value_name' => 'filterValue'  // filter value query parameter name
        ],
        'template' => [
            'pagination' => '@KnpPaginator/Pagination/sliding.html.twig',     // sliding pagination controls template
            'sortable' => '@KnpPaginator/Pagination/sortable_link.html.twig', // sort link template
            'filtration' => '@KnpPaginator/Pagination/filtration.html.twig'   // filters template
        ]
    ]);
};
```

#### Additional pagination templates
That could be used out of the box in `knp_paginator.template.pagination` key:

* `@KnpPaginator/Pagination/sliding.html.twig` (by default)
* `@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_v3_pagination.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_pagination.html.twig`
* `@KnpPaginator/Pagination/foundation_v5_pagination.html.twig`
* `@KnpPaginator/Pagination/bulma_pagination.html.twig`
* `@KnpPaginator/Pagination/semantic_ui_pagination.html.twig`
* `@KnpPaginator/Pagination/materialize_pagination.html.twig`
* `@KnpPaginator/Pagination/tailwindcss_pagination.html.twig`
* `@KnpPaginator/Pagination/uikit_v3_pagination.html.twig`

#### Additional sortable templates
That could be used out of the box in `knp_paginator.template.sortable` key:

* `@KnpPaginator/Pagination/sortable_link.html.twig` (by default)
* `@KnpPaginator/Pagination/twitter_bootstrap_v3_sortable_link.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_v4_font_awesome_sortable_link.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_v4_material_design_icons_sortable_link.html.twig`
* `@KnpPaginator/Pagination/semantic_ui_sortable_link.html.twig`
* `@KnpPaginator/Pagination/uikit_v3_sortable.html.twig`

## Usage examples:

### Controller

Currently paginator can paginate:

- `array`
- `Doctrine\ORM\Query`
- `Doctrine\ORM\QueryBuilder`
- `Doctrine\ODM\MongoDB\Query\Query`
- `Doctrine\ODM\MongoDB\Query\Builder`
- `Doctrine\ODM\PHPCR\Query\Query`
- `Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder`
- `Doctrine\Common\Collection\ArrayCollection` - any Doctrine relation collection including
- `ModelCriteria` - Propel ORM query
- array with `Solarium_Client` and `Solarium_Query_Select` as elements

```php
// App\Controller\ArticleController.php

public function listAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
{
    $dql   = "SELECT a FROM AcmeMainBundle:Article a";
    $query = $em->createQuery($dql);

    $pagination = $paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        10 /*limit per page*/
    );

    // parameters to template
    return $this->render('article/list.html.twig', ['pagination' => $pagination]);
}
```

### View

```twig
{# total items count #}
<div class="count">
    {{ pagination.getTotalItemCount }}
</div>
<table>
    <tr>
        {# sorting of properties based on query components #}
        <th>{{ knp_pagination_sortable(pagination, 'Id', 'a.id') }}</th>
        <th{% if pagination.isSorted('a.Title') %} class="sorted"{% endif %}>
            {{ knp_pagination_sortable(pagination, 'Title', 'a.title') }}
        </th>
        <th{% if pagination.isSorted(['a.date', 'a.time']) %} class="sorted"{% endif %}>
            {{ knp_pagination_sortable(pagination, 'Release', ['a.date', 'a.time']) }}
        </th>
    </tr>

    {# table body #}
    {% for article in pagination %}
        <tr {% if loop.index is odd %}class="color"{% endif %}>
            <td>{{ article.id }}</td>
            <td>{{ article.title }}</td>
            <td>{{ article.date | date('Y-m-d') }}, {{ article.time | date('H:i:s') }}</td>
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
* `%foo% name` with translation key `table_header_name`. The translation is in the domain `messages`.
* `{0} No author|{1} Author|[2,Inf] Authors` with translation key `table_header_author`. The translation is in the domain `messages`.

translationCount and translationParameters can be combined.

```twig
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

### Adding translation files
You can also override translations by creating a translation file in the following name format: `domain.locale.format`.
So, to create a translation file for this bundle you need to create for instance `KnpPaginatorBundle.tr.yaml` file under `project_root/translations/` 
and add your translations there:
```yaml
label_previous: "Önceki"
label_next: "Sonraki"
filter_searchword: "Arama kelimesi"
```
If you set default translation for configuration accordingly:
```yaml
framework:
    default_locale: tr
```
Symfony will pick it automatically.

### Dependency Injection

You can automatically inject a paginator service into another service by using the `knp_paginator.injectable` DIC tag.
The tag takes one optional argument `paginator`, which is the ID of the paginator service that should be injected.
It defaults to `knp_paginator`.

The class that receives the KnpPaginator service must implement `Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface`.
If you're too lazy you can also just extend the `Knp\Bundle\PaginatorBundle\Definition\PaginatorAware` base class.

> **⚠ Warning** using `PaginatorAwareInterface` is discouraged, and could be removed in a future version. You should not rely on setter
> injection, but only on proper constructor injection. Using Symfony built-in autowiring mechanism is the suggested way to go.

#### Lazy service

The `knp_paginator` service will be created lazily if the package `symfony/proxy-manager-bridge` is installed.

For more information about lazy services, consult the [Symfony documentation on dependency injection](https://symfony.com/doc/current/service_container/lazy_services.html).

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

[knp_component_pager]: https://github.com/KnpLabs/knp-components/blob/master/docs/pager/intro.md "Knp Pager component introduction"
[doc_custom_pagination_subscriber]: https://github.com/KnpLabs/KnpPaginatorBundle/tree/master/docs/custom_pagination_subscribers.md "Custom pagination subscribers"
[doc_templates]: https://github.com/KnpLabs/KnpPaginatorBundle/tree/master/docs/templates.md "Customizing Pagination templates"

## Troubleshooting

- Make sure the translator is activated in your Symfony config:

```yaml
framework:
    translator: { fallbacks: ['%locale%'] }
```

- If your locale is not available, create your own translation file in
`translations/KnpPaginatorBundle.en.yml` (substitute "en" for your own language code if needed).
Then add these lines:

```yaml
label_next: Next
label_previous: Previous
```

## Maintainers

Please read [this post](https://knplabs.com/en/blog/news-for-our-foss-projects-maintenance) first.

This library is maintained by the following people (alphabetically sorted) :
- @garak
- @polc
