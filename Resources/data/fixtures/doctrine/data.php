<?php
use Bundle\DoctrinePaginatorBundle\Document\Test;

for($it = 11; $it <= 55; $it++) {
    $varName = 'test'.$it;
    $$varName = new Test();
    $$varName->title = 'test '.$it;
}
