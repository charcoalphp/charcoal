<?php

declare (strict_types=1);
namespace Charcoal\MonorepoBuilder\Release\ReleaseWorker;

use PharIo\Version\Version;
use MonorepoBuilder202206\Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use MonorepoBuilder202206\Symplify\EasyCI\Exception\ShouldNotHappenException;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use MonorepoBuilder202206\Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Custom Release Worker to prevent overwriting the whole composer.json 'replace' section.
 * Prevents the locomotivemtl packages from being removed from the root composer.json file.
 */
final class UpdateReplaceReleaseWorker implements ReleaseWorkerInterface
{
    /**
     * @var \Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider
     */
    private $composerJsonProvider;
    /**
     * @var \Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager
     */
    private $jsonFileManager;
    public function __construct(ComposerJsonProvider $composerJsonProvider, JsonFileManager $jsonFileManager)
    {
        $this->composerJsonProvider = $composerJsonProvider;
        $this->jsonFileManager = $jsonFileManager;
    }
    public function work(Version $version) : void
    {
        $rootComposerJson = $this->composerJsonProvider->getRootComposerJson();
        $replace = $rootComposerJson->getReplace();
        $packageNames = $this->composerJsonProvider->getPackageNames();
        $newReplace = [];
        foreach (\array_keys($replace) as $package) {
            if (!\in_array($package, $packageNames, \true) || \strpos($package, 'locomotivemtl/charcoal-') !== 0) {
                continue;
            }
            $newReplace[$package] = $version->getVersionString();
        }
        if ($replace === $newReplace) {
            return;
        }
        $rootComposerJson->setReplace($newReplace);
        $rootFileInfo = $rootComposerJson->getFileInfo();
        if (!$rootFileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }
        $this->jsonFileManager->printJsonToFileInfo($rootComposerJson->getJsonArray(), $rootFileInfo);
    }
    public function getDescription(Version $version) : string
    {
        return 'Update "replace" version in "composer.json" to new tag to avoid circular dependencies conflicts';
    }
}