<?php

declare(strict_types=1);
/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.11.0|configurator
 * you can change this configuration by importing this file.
 */
$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'array_indentation' => true,
        'declare_strict_types' => true,
        'multiline_whitespace_before_semicolons' => false,
        'void_return' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude([
                'bootstrap/cache',
                'bower_components',
                'node_modules',
                'tasks',
                'public',
                'bin',
                'storage',
                'vendor',
            ])
            ->in(__DIR__)
            ->name('.*\.php')
            ->notName('*.blade.php')
    );
