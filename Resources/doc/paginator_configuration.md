# Configuring paginator

There's easy way to configure paginator - just add `knp_paginator` to `config.yml` and change default values.

## Default options

    knp_paginator:
        page_range: 5
        template:
            pagination: KnpPaginatorBundle:Pagination:sliding.html.twig
            sortable: KnpPaginatorBundle:Pagination:sortable_link.html.twig

