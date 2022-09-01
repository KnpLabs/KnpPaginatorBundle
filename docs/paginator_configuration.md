# Configuring paginator

There's an easy way to configure paginator - just add a `knp_paginator` entry to your configuration and change default values.
Location of your configuration file depends on your Symfony versions: most common places are `config/packages/knp_paginator.yaml`
for recent versions of Symfony and `app/config.yml` for older versions. If you can't find a configuration file, you can create it.

## Default options

``` yaml
knp_paginator:
    page_range: 5                      # default page range used in pagination control
    page_limit: 100                    # page limit for pagination control; to disable set this field to ~ (null)
    convert_exception: false           # convert paginator exception (e.g. non-positive page and/or limit) into 404 error
    default_options:
        page_name: page                # page query parameter name
        sort_field_name: sort          # sort field query parameter name; to disable sorting set this field to ~ (null)
        sort_direction_name: direction # sort direction query parameter name
        distinct: true                 # ensure distinct results, useful when ORM queries are using GROUP BY statements
        page_out_of_range: ignore      # if page number exceeds the last page. Options: 'fix'(return last page); 'throwException'
        default_limit: 10              # default number of items per page
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
