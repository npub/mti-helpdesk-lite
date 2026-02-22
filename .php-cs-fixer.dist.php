<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

// @see https://cs.symfony.com
// @see https://cs.symfony.com/doc/ruleSets/index.html
// @see https://cs.symfony.com/doc/config.html

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect()) // @TODO 4.0 no need to call this manually
    ->setRiskyAllowed(true)
    ->setRules([
        '@auto' => true,
        '@auto:risky' => true,

        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        '@PER-CS2x0' => true,
        '@PER-CS2x0:risky' => true,

        '@PSR12' => true,
        '@PSR12:risky' => true,

        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,

        '@PHP5x4Migration' => true,
        '@PHP5x6Migration:risky' => true,
        '@PHP7x0Migration' => true,
        '@PHP7x0Migration:risky' => true,
        '@PHP7x1Migration' => true,
        '@PHP7x1Migration:risky' => true,
        '@PHP7x3Migration' => true,
        '@PHP7x4Migration' => true,
        '@PHP7x4Migration:risky' => true,
        '@PHP8x0Migration' => true,
        '@PHP8x0Migration:risky' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x2Migration:risky' => true,
        // '@PHP8x3Migration' => true,  // Отключил для обратной совместимости с PHP 8.2+ (согласно ТЗ)
        // '@PHP8x4Migration' => true,
        // '@PHP8x5Migration' => true,

        '@PHPUnit10x0Migration:risky' => true,

        'concat_space' => ['spacing' => 'one'],
        'no_empty_comment' => true,
        'phpdoc_to_comment' => false,
        'return_to_yield_from' => true,

        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
    ])
    // 💡 by default, Fixer looks for `*.php` files excluding `./vendor/` - here, you can groom this config
    ->setFinder(
        (new Finder())
            // 💡 root folder to check
            ->in(
                __DIR__ . '/src',
                // __DIR__ . '/tests/Controller',
            )
            // 💡 additional files, eg bin entry file
            // ->append([__DIR__.'/bin-entry-file'])
            // 💡 folders to exclude, if any
            // ->exclude([/* ... */])
            // 💡 path patterns to exclude, if any
            // ->notPath([/* ... */])
            // 💡 extra configs
            // ->ignoreDotFiles(false) // true by default in v3, false in v4 or future mode
            // ->ignoreVCS(true) // true by default
    )
    ->setUnsupportedPhpVersionAllowed(true)
;
