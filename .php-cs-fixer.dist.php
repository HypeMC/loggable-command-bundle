<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->append([__FILE__])
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setUsingCache(true)
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PHP71Migration' => true,
        '@PHPUnit100Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'declare_strict_types' => true,
        'heredoc_to_nowdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
        'phpdoc_separation' => [
            'groups' => [['Common\\*']],
        ],
        'self_static_accessor' => true,
        'single_line_throw' => false,
        'trailing_comma_in_multiline' => ['after_heredoc' => true, 'elements' => ['array_destructuring', 'arrays']],
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
        // PHP 7 compatibility
        'get_class_to_class_keyword' => false,
        'modernize_strpos' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
