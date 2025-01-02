<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'no_alternative_syntax' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'yoda_style' => false,
        'indentation_type' => true,
    ])
    ->setIndent("\t")
    ->setFinder($finder)
    ->setUsingCache(false)
    ->setRiskyAllowed(true);
