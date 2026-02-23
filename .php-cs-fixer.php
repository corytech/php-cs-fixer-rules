<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor'])
    ->name('*.php')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'heredoc_to_nowdoc' => false,
        'phpdoc_annotation_without_dot' => false,
        'return_type_declaration' => true,
        'cast_spaces' => ['space' => 'single'],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'single_line_throw' => false,
        'declare_strict_types' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'class_definition' => [
            'single_line' => false,
        ],
        'phpdoc_to_comment' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
