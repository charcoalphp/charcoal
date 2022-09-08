<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // where are the packages located?
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        // default value
        __DIR__.'/packages',
    ]);

    // for "merge" command.
    $parameters->set(Option::DATA_TO_APPEND, [
        ComposerJsonSection::REQUIRE_DEV => [
            'phpunit/phpunit' => '^9.5',
        ],
        ComposerJsonSection::REPLACE => [
            'locomotivemtl/charcoal-admin' => '*',
            'locomotivemtl/charcoal-app' => '*',
            'locomotivemtl/charcoal-attachment' => '*',
            'locomotivemtl/charcoal-cache' => '*',
            'locomotivemtl/charcoal-cms' => '*',
            'locomotivemtl/charcoal-config' => '*',
            'locomotivemtl/charcoal-core' => '*',
            'locomotivemtl/charcoal-email' => '*',
            'locomotivemtl/charcoal-factory' => '*',
            'locomotivemtl/charcoal-image' => '*',
            'locomotivemtl/charcoal-object' => '*',
            'locomotivemtl/charcoal-property' => '*',
            'locomotivemtl/charcoal-queue' => '*',
            'locomotivemtl/charcoal-translator' => '*',
            'locomotivemtl/charcoal-ui' => '*',
            'locomotivemtl/charcoal-user' => '*',
            'locomotivemtl/charcoal-view' => '*'
        ],
    ]);

    $services = $containerConfigurator->services();

    # release workers - in order to execute
    $services->set(UpdateReplaceReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);

    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
};
