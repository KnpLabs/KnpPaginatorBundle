<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(array(__DIR__))
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        '-unalign_equals',
        'newline_after_open_tag',
        'ordered_use',
        'long_array_syntax',
    ))
    ->setUsingCache(true)
    ->finder($finder)
;
