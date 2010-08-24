Provides pagination for your Doctrine ODM + Symfony2 Project.

As for now, it only works for ODM; but ORM may be also supported later.

## Installation

### Add DoctrinePaginatorBundle to your src/Bundle dir

    git submodule add git://github.com/knplabs/DoctrinePaginatorBundle.git src/Bundle/DoctrinePaginatorBundle

### Add DoctrineUserBundle to your application kernel

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Bundle\DoctrinePaginatorBundle\DoctrinePaginatorBundle(),
            // ...
        );
    }

### Enable the paginator

    # app/config.yml
    doctrine_paginator.config: ~
