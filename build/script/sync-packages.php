#!/usr/bin/env php
<?php

/**
 * Synchronize common files across Charcoal's packages.
 *
 * Based on {@see https://github.com/symfony/symfony/blob/v8.0.5/.github/sync-packages.php}
 */

if (PHP_SAPI !== 'cli') {
    echo "This script can only be run from the command line.";
    exit(1);
}

// Strip extension from path to this script.
$syncDir = substr(__FILE__, 0, -4);

$ghPullRequestTemplate = file_get_contents($syncDir . '/PULL_REQUEST_TEMPLATE.md');
$ghClosePullRequestWorkflow = file_get_contents($syncDir . '/close-pull-request.yml');
$commonAttributes = [
    [ '/.github/',             'export-ignore' ],
    [ '/tests/',               'export-ignore' ],
    [ '.editorconfig',         'export-ignore' ],
    [ '.gitattributes',        'export-ignore' ],
    [ '.gitignore',            'export-ignore' ],
    [ 'phpcs.xml.dist',        'export-ignore' ],
    [ 'phpstan-baseline.neon', 'export-ignore' ],
    [ 'phpstan.neon.dist',     'export-ignore' ],
    [ 'phpunit.xml.dist',      'export-ignore' ],
];
$commonIgnores = [
    '/.idea/',
    '/.vscode/',
    '/node_modules/',
    '/vendor/',
    '*.log',
    '.DS_store',
    '.phpunit.cache',
    '.phpunit.result.cache',
    'composer.lock',
    'composer.phar',
    'phpcs.xml',
    'phpstan.neon',
    'phpunit.xml',
    'psalm.xml',
    'Thumbs.db',
];

$commonAttributesRegExp = [];
$commonAttributesString = [];
foreach ($commonAttributes as $attribute) {
    $commonAttributesRegExp[] = preg_quote($attribute[0], '#');
    $commonAttributesString[] = implode(' ', $attribute);
}

$commonAttributesRegExp = implode('|', $commonAttributesRegExp);
$commonAttributesString = implode("\n", $commonAttributesString);

foreach (glob('packages/*/composer.json') as $package) {
    $package = dirname($package);

    $attributes = (string) file_get_contents($package . '/.gitattributes');
    $attributes = preg_replace(
        '#^(' . $commonAttributesRegExp . ')\s+export-ignore\n#m',
        '',
        $attributes
    );
    $attributes .= "{$commonAttributesString}\n";
    file_put_contents($package . '/.gitattributes', $attributes);

    $ignores = file($package . '/.gitignore', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($ignores) {
        $ignores = array_filter($ignores, fn(string $line): bool => $line[0] !== '#');
        $ignores = array_unique(array_merge($ignores, $commonIgnores));
        usort($ignores, 'sort_by_path_rank');
        $ignores = implode("\n", $ignores);
    } else {
        $ignores = implode("\n", $commonIgnores);
    }
    file_put_contents($package . '/.gitignore', $ignores);

    if ($ghPullRequestTemplate || $ghClosePullRequestWorkflow) {
        @mkdir($package . '/.github');

        if ($ghPullRequestTemplate) {
            file_put_contents($package . '/.github/PULL_REQUEST_TEMPLATE.md', $ghPullRequestTemplate);
        }

        if ($ghClosePullRequestWorkflow) {
            @mkdir($package . '/.github/workflows');
            file_put_contents($package . '/.github/workflows/close-pull-request.yml', $ghClosePullRequestWorkflow);
        }
    }
}

/**
 * For sorting a list of directories and files.
 *
 * Prioritize directories first, then files.
 */
function get_path_rank(string $path): int
{
    if (str_starts_with($path, '/')) {
        return -2;
    }

    if (str_starts_with($path, '.')) {
        return -1;
    }

    return 0;
}

function sort_by_path_rank(string $a, $b): int
{
    $ar = get_path_rank($a);
    $br = get_path_rank($a);

    return $a <=> $b;
}
