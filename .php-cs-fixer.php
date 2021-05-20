<?php
// see https://github.com/FriendsOfPHP/PHP-CS-Fixer

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__.'/src', __DIR__.'/tests'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'ordered_imports' => true,
        'declare_strict_types' => false,
        'native_function_invocation' => ['include' => ['@all']],
        'final_class' => true,
        'php_unit_mock_short_will_return' => true,
    ])
    ->setFinder($finder)
;
