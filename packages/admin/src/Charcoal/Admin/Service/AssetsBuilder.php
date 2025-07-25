<?php

namespace Charcoal\Admin\Service;

// from charcoal-admin
use Charcoal\Admin\AssetsConfig;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * Assets Builder
 *
 * Build custom assets builder using Symfony Asset component
 */
final class AssetsBuilder
{
    /**
     * @var Packages|null
     */
    private $packages = null;

    /**
     * @var string|null
     */
    private $basePath = null;

    /**
     * @param  string|null $basePath The assets base path.
     * @return void
     */
    public function __construct($basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * Alias of {@see self::build()}.
     *
     * @param  AssetsConfig $config The assets management config.
     * @return Packages
     */
    public function __invoke(AssetsConfig $config)
    {
        return $this->build($config);
    }

    /**
     * @param  AssetsConfig $config The assets management config.
     * @return Packages
     */
    public function build(AssetsConfig $config)
    {
        $versionStrategy = new EmptyVersionStrategy();
        $package = new Package($versionStrategy);
        $this->packages = new Packages($package);
        // Optionally, you can add more packages for different base paths or versioning
        return $this->packages;
    }

    /**
     * Get asset URLs for a collection.
     *
     * @param array $collections Assets collections.
     * @return array
     */
    public function getAssetUrls(array $collections)
    {
        $urls = [];
        foreach ($collections as $collectionIdent => $actions) {
            $files = ($actions['files'] ?? []);
            // Parse scoped files. Solves merging issues.
            array_walk($actions, function ($scope) use (&$files) {
                if (isset($scope['files']) && !empty($scope['files'])) {
                    $files = array_merge($files, $scope['files']);
                }
            });
            $files = array_unique($files);
            $urls[$collectionIdent] = $this->generateUrls($files);
        }
        return $urls;
    }

    /**
     * Generate asset URLs from file paths.
     *
     * @param string[] $files
     * @return string[]
     */
    private function generateUrls(array $files = [])
    {
        $urls = [];
        foreach ($files as $file) {
            $urls[] = $this->packages->getUrl($file);
        }
        return $urls;
    }
}
