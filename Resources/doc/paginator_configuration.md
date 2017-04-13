# Configuring paginator

There's easy way to configure paginator - just add `knp_paginator` to `config.yml` and change default values.

## Default options

``` yaml
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

There are a few additional pagination templates, that could be used out of the box in `knp_paginator.template.pagination` key:

* `KnpPaginatorBundle:Pagination:sliding.html.twig` (by default)
* `KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig`
* `KnpPaginatorBundle:Pagination:twitter_bootstrap_pagination.html.twig`
