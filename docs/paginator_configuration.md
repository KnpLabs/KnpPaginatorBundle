# Configuring paginator

There's an easy way to configure paginator - just add a `knp_paginator` entry to your configuration and change default values.
Location of your configuration file depends on your Symfony versions: most common places are `config/packages/knp_paginator.yaml`
for recent versions of Symfony and `app/config.yml` for older versions. If you can't find a configuration file, you can create it.

## Default options

``` yaml
knp_paginator:
    page_range: 5                      # default page range used in pagination control
    page_limit: 100                    # page limit for pagination control; to disable set this field to ~ (null)
    default_options:
        page_name: page                # page query parameter name
        sort_field_name: sort          # sort field query parameter name; to disable sorting set this field to ~ (null)
        sort_direction_name: direction # sort direction query parameter name
        distinct: true                 # ensure distinct results, useful when ORM queries are using GROUP BY statements
        page_out_of_range: ignore      # if page number exceeds the last page. Options: 'fix'(return last page); 'throwException'
        default_limit: 10              # default number of items per page
    custom_parameters: {}              # custom parameters that are passed to pagination template
    template:
        pagination: @KnpPaginator/Pagination/sliding.html.twig     # sliding pagination controls template
        sortable: @KnpPaginator/Pagination/sortable_link.html.twig # sort link template
```

There are a few additional pagination templates, that could be used out of the box in `knp_paginator.template.pagination` key:

* `@KnpPaginator/Pagination/sliding.html.twig` (by default)
* `@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_v3_pagination.html.twig`
* `@KnpPaginator/Pagination/twitter_bootstrap_pagination.html.twig`
* `@KnpPaginator/Pagination/foundation_v6_pagination.html.twig`
* `@KnpPaginator/Pagination/foundation_v5_pagination.html.twig`
* `@KnpPaginator/Pagination/bulma_pagination.html.twig`

## Custom parameters

The `custom_parameters` setting allows to set template variables, that are used
in the pagination template, globally.

For example some templates use the `align` variable to adjust the positioning of
the pagination widget. You can set it globally this way:

``` yaml
knp_paginator:
    custom_parameters:
        align: center
```
