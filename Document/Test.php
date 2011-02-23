<?php

namespace Knplabs\PaginatorBundle\Document;

/**
 * @mongodb:Document(collection="knplabs_paginator_test")
 */
class Test
{
    /**
     * @mongodb:Id
     */
    public $id;

    /**
     * @mongodb:Field(type="string")
     */
    public $title;
}
