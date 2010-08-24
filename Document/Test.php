<?php

namespace Bundle\DoctrinePaginatorBundle\Document;

/**
 * @Document(collection="doctrine_paginator_test")
 */
class Test
{
    /**
     * @Id
     */
    public $id;

    /**
     * @Field(type="string")
     */
    public $title;
}
