
# Templates

This document will describe how pagination can be rendered, extended and used in
templates. For now there's only a sliding pagination supported, so all documentation
will reference it.

## Overriding default pagination template

There are few ways to override templates

### Configuration

This way it will override it globally for all default pagination rendering.

You can override templates in [configuration of
paginator](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/README.md#configuration)

Or place this parameter in **config/packages/knp_paginator.yaml**

    knp_paginator.template.pagination: my_pagination.html.twig

Same for sorting link template:

    knp_paginator.template.sortable: my_sortable.html.twig

### Directly in pagination

``` php
$paginator = $this->get('knp_paginator');
$pagination = $paginator->paginate($target, $page);
$pagination->setTemplate('my_pagination.html.twig');
$pagination->setSortableTemplate('my_sortable.html.twig');
```

or in view

``` html
{% do pagination.setTemplate('my_pagination.html.twig') %}
```

### In render method

```twig
{{ knp_pagination_render(pagination, 'my_pagination.html.twig') }}
```

or by specifying path to your custom template that is located under your project's `templates` directory:
```twig
{{ knp_pagination_sortable(pagination, 'date', 'c.publishedAt', {}, {}, 'KnpPaginator/Pagination/bootstrap_v4_sortable_link.html.twig') }}
{{ knp_pagination_render(pagination, 'KnpPaginator/Pagination/bootstrap_v4_pagination.html.twig') }}
```

## Other useful parameters

By default when render method is triggered, pagination renders the template
with standard arguments provided:

- pagination parameters, like pages in range, current page and so on..
- route - which is used to generate page, sorting urls
- request query, which contains all GET request parameters
- extra pagination template parameters

Except from pagination parameters, others can be modified or adapted to some
use cases. Usually it's possible, you might need setting a route if default is not
matched correctly (because of rendering in sub requests). Or adding additional
query or view parameters.

### Setting a route and query parameters to use for pagination urls

``` php
<?php
$paginator = $this->get('knp_paginator');
$pagination = $paginator->paginate($target, $page);
$pagination->setUsedRoute('blog_articles');
```

In case if route requires additional parameters

``` php
<?php
$pagination->setParam('category', 'news');
```

This would set additional query parameter

### Additional pagination template parameters

If you need custom parameters in pagination template, use:

``` php
<?php
// set an array of custom parameters
$pagination->setCustomParameters([
    'align' => 'center', # center|right (for template: twitter_bootstrap_v4_pagination and foundation_v6_pagination)
    'size' => 'large', # small|large (for template: twitter_bootstrap_v4_pagination)
    'style' => 'bottom',
    'span_class' => 'whatever',
]);
```

### You can also change the page range

Default page range is 5 pages in sliding pagination. Doing it in controller:

``` php
<?php
$pagination->setPageRange(7);
```

In template:

``` php
{% do pagination.setPageRange(7) %}
```

### Setting a page limit

The default page limit is unlimited, but if for some reason you want to limit
the number of pages you can do so. Doing it in controller:

``` php
<?php
$pagination->setPageLimit(25);
```

In template:

``` php
{% do pagination.setPageLimit(25) %}
```

## Choose the sorting direction

The `knp_pagination_sortable()` template automatically switches the sorting direction but sometimes you need to propose that your users select the sorting direction.
You can add an array at the end of `knp_pagination_sortable()` to choose the direction.

``` html
{{ knp_pagination_sortable(pagination, 'Title A-Z', 'a.title', {}, {'direction': 'asc'}) }}
{{ knp_pagination_sortable(pagination, 'Title Z-A', 'a.title', {}, {'direction': 'desc'}) }}
```
(Assuming you use the default configuration value of sort_direction_name)

<a name="query-parameters"></a>

## Query parameters

If you need to change query parameters for paginator or use multiple paginators for the same page.
You can customize these parameter names through [configuration](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/README.md#configuration)
or manually with paginator options.

``` php
<?php // controller

// will change "page" query parameter into "section" and sort direction "direction" into "dir"
$paginator = $this->get('knp_paginator');
$pagination = $paginator->paginate(
    $query, // target to paginate
    $this->get('request')->query->getInt('section', 1), // page parameter, now section
    10, // limit per page
    ['pageParameterName' => 'section', 'sortDirectionParameterName' => 'dir']
);
```

Or even in Twig:

```jinja
    {{ knp_pagination_render(
            pagination,
            '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig',
            {
                'queryParam1': 'param1 value',
                'queryParam2': 'param2 value'
            },
            {
                'viewParam1': 'param1 value',
                'viewParam2': 'param2 value'
            },
        ) }}
```

## Filter your query

Only include this lines and enjoy the pagination :
``` html
{{ knp_pagination_filter(pagination, {
        'entity.name': 'Name',
    }) }}
```

## Customize rendering

### Bulma

You can configure the position, the size, and make the buttons rounded or not:
- `align`: `'left'`, `'center'`, or `'right'`. By default align is not modified
- `size`: `'small'`, `'medium'`, or `'large'`. By default, size is not modified
- `rounded`: `true` or `false`. By default it's `false`

In your controller:
```php
$pagination->setCustomParameters([
    'align' => 'center',
    'size' => 'large',
    'rounded' => true,
]);
```

or in the view:
```twig
{{ knp_pagination_render(pagination, null, {}, {
   'align': 'center',
   'size': 'large',
   'rounded': true,
}) }}
```
