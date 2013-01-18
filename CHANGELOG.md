**2013-01-19**
- Removed Twig extenstion methods: `render()` & `sortable()`,
- Pagination subscribers no longer depend on `request` scope.

**2012-07-06**

- Added method `isSorted` to the `SlidingPagination` to enable views to know if
a given column is currently sorted.

**2012-03-23**

- Changed the behavior of customization for query parameters. Etc. now there is no more **alias**
for paginations. Instead it will use organized parameter names, which can be set for each pagination
as different or configured in default global scope, see the [documentation](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/README.md#configuration)
and [upgrade
guide](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/Resources/doc/upgrade.md)
make sure you use **twig at least version 1.5**

- If you do not wish to migrate to these new changes. Checkout paginator bundle at **v2.1** tag and
komponents at **v1.0**

**2012-03-02**

- Added support for [Solarium](http://solarium-project.org), a PHP library that handles [Solr](http://lucene.apache.org/solr/) search.

**2011-12-16**

- Joined **count** and **items** events into one **items** which now populates
count and item result on event. This way it is more straightforward and cleaner

**2011-12-09**

- Changed event names to more distinctive. Using main symfony event dispatcher service.
- Optimazed event properties for usage by reference

**2011-12-05**

- Recently there was a change in repository vendor name: **knplabs** --> **KnpLabs**
be sure to update your remotes accordingly. etc: github.com/**knplabs**/KnpPaginatorBundle.git
to github.com/**KnpLabs**/KnpPaginatorBundle.git.
- One-liner: `git remote set-url origin http://github.com/KnpLabs/KnpPaginatorBundle.git`
