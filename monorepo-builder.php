<?php

declare(strict_types=1);

use Charcoal\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;

return static function (MBConfig $mbConfig): void {
    // where are the packages located?
    $mbConfig->packageDirectories([
        // default value
        __DIR__ . '/packages',
    ]);

    // for "merge" command.
    $mbConfig->dataToAppend([
        ComposerJsonSection::REQUIRE_DEV => [
            'phpunit/phpunit' => '^9.5',
        ],
    ]);

    $mbConfig->packageAliasFormat('<major>.x-dev');
    $mbConfig->defaultBranch('main');

    # release workers - in order to execute
    $mbConfig->workers([
        SetCurrentMutualDependenciesReleaseWorker::class,
        UpdateBranchAliasReleaseWorker::class,
    ]);
};
