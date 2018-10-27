# 2.8.0
*Released at 2018-05-16*

### New features
- Updated Bulma pagination template for version 0.6.x
- Added Ukrainian translation
- Added size option for Bootstrap v4

### Bugfixes
- Add PHP 7.2 to test matrix
- Some `README.md` fixes

# 2.7.2
*Released at 2018-01-30*

### New features
- Added Arabic translation and documentation about translation troubleshooting
- Added Brazilian Portuguese translation

# 2.7.1
*Released at 2017-12-01*

### Bugfixes
- the `knp_paginator` service is now really marked as public #462

# 2.7.0
*Released at 2017-12-01*

### New features
- Symfony 4 support
- Prevent warning in Symfony 3.3 DI
- Twitter bootstrap 4 alignment
- Twitter bootstrap 3 sortable links
- PHPUnit 6 compatibility
- Added translations
  - Danish
  - Hungarian
  - Persian
  - Swedish
  
### Changes
- The `knp_paginator` service is now marked as lazy
- Use paginator option for direction parameter name
- Allow `isSorted()` to have no arguments
- Use modern twig namespace notation

### Bugfixes
- Fix invalid index
- Various documentation fixes
- Running tests against PHP 5.3

# 2.6
*Released at 2017-06-09*

- Drop support for unmaintained versions of symfony
- Fix readme / doc
- Add translations for id, it, lt, nl and sw
- Improve pagination template for Semantic
- Add support for Bootstrap 4 (alpha 6)

# 2.5.4
*Released at 2017-03-21*

### New features
- Added bulma css paginator template

# 2.5.3
*Released at 2016-04-20*
### Bugfixes
- Regression was introduced

# 2.5.2
*Released at 2016-04-20*

### Bugfixes
- Updated composer dev version

# 2.5.1
*Released at 2015-11-23*

### New features
- Symfony 3 and Twig 2 compatibility

# 2.5.0
*Released at 2015-09-17*

### New features
- Added Foundation 5 Sliding pagination control implementation template

# 2.4.2
*Released at 2015-06-09*

### New features
- Uses more relevant method to get current page value

# 2.4.1
*Released at 2014-09-15*

# 2.4.0
*Released at 2014-01-21*

# 2.3.3
*Released at 2013-07-30*

### New features
- Added twitter bootstrap3 support
- Improved `README.md`

# 2.3.2
*Released at 2013-02-19*

# 2013-01-19

- Removed Twig extenstion methods: `render()` & `sortable()`,
- Pagination subscribers no longer depend on `request` scope.

# 2012-07-06

- Added method `isSorted` to the `SlidingPagination` to enable views to know if
a given column is currently sorted.

# 2012-03-23

- Changed the behavior of customization for query parameters. Etc. now there is no more **alias**
for paginations. Instead it will use organized parameter names, which can be set for each pagination
as different or configured in default global scope, see the [documentation](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/README.md#configuration)
and [upgrade
guide](http://github.com/KnpLabs/KnpPaginatorBundle/blob/master/Resources/doc/upgrade.md)
make sure you use **twig at least version 1.5**

- If you do not wish to migrate to these new changes. Checkout paginator bundle at **v2.1** tag and
komponents at **v1.0**

# 2012-03-02

- Added support for [Solarium](http://solarium-project.org), a PHP library that handles [Solr](http://lucene.apache.org/solr/) search.

# 2.0
*Released at 2012-01-19*

# 2011-12-16

- Joined **count** and **items** events into one **items** which now populates
count and item result on event. This way it is more straightforward and cleaner

# 2011-12-09

- Changed event names to more distinctive. Using main symfony event dispatcher service.
- Optimazed event properties for usage by reference

# 2011-12-05

- Recently there was a change in repository vendor name: **knplabs** --> **KnpLabs**
be sure to update your remotes accordingly. etc: github.com/**knplabs**/KnpPaginatorBundle.git
to github.com/**KnpLabs**/KnpPaginatorBundle.git.
- One-liner: `git remote set-url origin http://github.com/KnpLabs/KnpPaginatorBundle.git`

# 1.0
*Released at 2011-11-13*
