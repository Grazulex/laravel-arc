<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        
        // Array formatting
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        
        // Import ordering
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        
        // Strict types
        'declare_strict_types' => false, // Keep false for Laravel package compatibility
        
        // PhpDoc
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_var_annotation_correct_order' => true,
        
        // Operators
        'binary_operator_spaces' => ['default' => 'single_space'],
        'concat_space' => ['spacing' => 'one'],
        
        // Control structures
        'yoda_style' => false,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        
        // Functions
        'return_type_declaration' => ['space_before' => 'none'],
        'single_line_throw' => false,
        
        // Strings
        'single_quote' => true,
        'escape_implicit_backslashes' => false,
        
        // Whitespace
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try'],
        ],
        
        // Comments
        'comment_to_phpdoc' => true,
        'multiline_comment_opening_closing' => true,
        
        // Specific Laravel/Package rules
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_test_annotation' => ['style' => 'prefix'],
        'no_alias_functions' => false, // Allow some PHP aliases for compatibility
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);

