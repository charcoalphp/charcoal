<?php

declare(strict_types=1);

namespace Charcoal\MonorepoBuilder\Release\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\DevMasterAliasUpdater;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Utils\VersionUtils;

/**
 * Custom Release Worker to set branch alias for all packages and the root composer.json file.
 */
final class UpdateBranchAliasReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @var \Symplify\MonorepoBuilder\DevMasterAliasUpdater
     */
    private $devMasterAliasUpdater;

    /**
     * @var \Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider
     */
    private $composerJsonProvider;

    /**
     * @var \Symplify\MonorepoBuilder\Utils\VersionUtils
     */
    private $versionUtils;

    public function __construct(DevMasterAliasUpdater $devMasterAliasUpdater, ComposerJsonProvider $composerJsonProvider, VersionUtils $versionUtils)
    {
        $this->devMasterAliasUpdater = $devMasterAliasUpdater;
        $this->composerJsonProvider = $composerJsonProvider;
        $this->versionUtils = $versionUtils;
    }

    public function work(Version $version) : void
    {
        $nextAlias = $this->versionUtils->getNextAliasFormat($version);
        $this->devMasterAliasUpdater->updateFileInfosWithAlias($this->composerJsonProvider->getRootAndPackageFileInfos(), $nextAlias);
    }

    public function getDescription(Version $version) : string
    {
        $nextAlias = $this->versionUtils->getNextAliasFormat($version);
        return \sprintf('Set branch alias "%s" to all packages and root package for Charcoal.', $nextAlias);
    }
}
