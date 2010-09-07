<?php

if(isset($dm)) {
    for($it = 11; $it <= 55; $it++) {
        $varName = 'test'.$it;
        $$varName = new Bundle\DoctrinePaginatorBundle\Document\Test();
        $$varName->title = 'test '.$it;
    }
}
