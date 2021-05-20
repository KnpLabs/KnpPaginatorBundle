# Creating custom subscriber

Lets say we want to paginate a directory content, might be quite interesting.
And when we have such a handy **Finder** component in symfony, it's easily achievable.

## Prepare environment

I will assume we you just installed [Symfony demo](https://github.com/symfony/demo)
and you install [KnpPaginatorBundle](https://github.com/knplabs/KnpPaginatorBundle).
Follow the installation guide on these repositories, it's very easy to set up.

## Create subscriber

Next, let's extend our subscriber.
Create a file named **src/Subscriber/PaginateDirectorySubscriber.php**

``` php
<?php

// file: src/Subscriber/PaginateDirectorySubscriber.php
// requires Symfony\Component\Finder\Finder

namespace App\Subscriber;

use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

final class PaginateDirectorySubscriber implements EventSubscriberInterface
{
    public function items(ItemsEvent $event): void
    {
        if (!is_string($event->target) || !is_dir($event->target)) {
            return;
        }
        $finder = new Finder();
        $finder
            ->files()
            ->depth('< 4') // 3 levels
            ->in($event->target)
        ;
        $iterator = $finder->getIterator();
        $files = iterator_to_array($iterator);
        $event->count = count($files);
        $event->items = array_slice($files, $event->getOffset(), $event->getLimit());
        $event->stopPropagation();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'knp_pager.items' => ['items', 1/* increased priority to override any internal */]
        ];
    }
}
```

Class above is the simple event subscriber, which listens to **knp_pager.items** event.
Creates a finder and looks in this directory for files. To be more specific it will look
for the **files** in the directory being paginated, max in 3 level depth.

## Register subscriber as service

Next we need to tell **knp_paginator** about our new fancy subscriber which we intend
to use in pagination. It is also very simple, add few line to your service config file
(usually **config/services.xml**)

``` xml
<?xml version="1.0" ?>

<!-- file: config/services.xml -->

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <!-- ... -->
    
        <service id="acme.directory.subscriber" class="App\Subscriber\PaginateDirectorySubscriber">
            <tag name="kernel.event_subscriber" />
        </service>
    </services>
</container>
```

## Controller action

Finally, we are done with configuration, now let's create actual controller action.
Modify controller: **src/Controller/DemoController.php**
And add the following action, which paginates the previous directory

``` php
<?php

use ...

/**
 * @Route("/test", name="_demo_test")
 */
public function test(KnpPaginatorInterface $paginator, Request $request): Response
{
    $pagination = $paginator->paginate(__DIR__.'/../', $request->query->getInt('page', 1), 10);
    
    return $this->render('demo/test.html.twig', ['pagination' => $pagination]);
}
```

## Template

And the last thing is the template, create: **templates/demo/test.html.twig**

``` html
{% extends "layout.html.twig" %}

{% block title "My demo" %}

{% block content %}
    <h1>Demo</h1>

    <table>
        <tr>
            {# sorting of properties based on query components #}
            <th>base name</th>
            <th>path</th>
        </tr>
    
        {# table body #}
        {% for file in pagination %}
            <tr{% if loop.index is odd %} class="color"{% endif %}>
                <td>{{ file.baseName }}</td>
                <td>{{ file.path }}</td>
            </tr>
        {% endfor %}
    </table>
    {# display navigation #}
    <div id="navigation">
        {{ knp_pagination_render(pagination) }}
    </div>
{% endblock %}
```

Do not forget to reload the cache: **php bin/console cache:clear**

You should find some files paginated if you open the url: **http://baseurl/index.php/demo/test**
