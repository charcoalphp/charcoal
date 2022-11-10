<?php

declare(strict_types=1);

use Charcoal\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Charcoal\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;

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

    # release workers - in order to execute
    $mbConfig->workers([
        UpdateReplaceReleaseWorker::class,
        SetCurrentMutualDependenciesReleaseWorker::class,

        // TagVersionReleaseWorker::class,
        // PushTagReleaseWorker::class,

        // SetNextMutualDependenciesReleaseWorker::class,
        UpdateBranchAliasReleaseWorker::class,
    ]);
};
