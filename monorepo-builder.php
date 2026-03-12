<?php

declare(strict_types=1);

use Charcoal\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;

return static function (MBConfig $mbConfig): void {
    $mbConfig->packageDirectories([
        __DIR__ . '/packages',
    ]);

    // Change only the major version of the branch-alias.
    $mbConfig->packageAliasFormat('<major>.x-dev');
    $mbConfig->defaultBranch('main');

    // Release workers are executed consecutively.
    $mbConfig->workers([
        SetCurrentMutualDependenciesReleaseWorker::class,
        UpdateBranchAliasReleaseWorker::class,
    ]);
};
