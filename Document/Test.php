<?php

namespace Bundle\DoctrinePaginatorBundle\Document;

/**
 * @mongodb:Document(collection="doctrine_paginator_test")
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
