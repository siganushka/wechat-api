<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP71Migration' => true,
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'declare_strict_types' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
