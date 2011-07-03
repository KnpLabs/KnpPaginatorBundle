<?php

if(isset($dm)) {
    for($it = 11; $it <= 55; $it++) {
        $varName = 'test'.$it;
        $$varName = new Knp\Bundle\PaginatorBundle\Document\Test();
        $$varName->title = 'test '.$it;
    }
}
