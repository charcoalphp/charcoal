<?php

namespace Charcoal\Admin\Service;

// from kriswallsmith/assetic
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use Assetic\Asset\AssetReference;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\AssetManager;
// from charcoal-admin
use Charcoal\Admin\AssetsConfig;

/**
 * Assets Builder
 *
 * Build custom assets builder using {@link https://github.com/kriswallsmith/assetic}
 */
final class AssetsBuilder
{
    private ?\Assetic\AssetManager $assetManager = null;

    /**
     * @param  string|null $basePath The assets base path.
     */
    public function __construct(private $basePath = null)
    {
    }

    /**
     * Alias of {@see self::build()}.
     *
     * @param  AssetsConfig $config The assets management config.
     * @return AssetManager
     */
    public function __invoke(AssetsConfig $config): ?\Assetic\AssetManager
    {
        return $this->build($config);
    }

    /**
     * @param  AssetsConfig $config The assets management config.
     * @return AssetManager
     */
    public function build(AssetsConfig $config): ?\Assetic\AssetManager
    {
        $this->assetManager = new AssetManager();
        $this->parseCollections($config->collections());

        return $this->assetManager;
    }

    /**
     * @param array $collections Assets collections.
     */
    private function parseCollections(array $collections): void
    {
        foreach ($collections as $collectionIdent => $actions) {
            $files = ($actions['files'] ?? []);
            // Parse scoped files. Solves merging issues.
            array_walk($actions, function (array $scope) use (&$files): void {
                if (isset($scope['files']) && !empty($scope['files'])) {
                    $files = array_merge($files, $scope['files']);
                }
            });

            $files = array_unique($files);
            $collection = $this->extractFiles($files);

            $ac = new AssetCollection($collection);
            $this->assetManager->set($collectionIdent, $ac);
        }
    }

    /**
     * @param  string[] $files Files to convert to Collection assets.
     * @return AssetInterface[]
     */
    private function extractFiles(array $files = []): array
    {
        $collection = [];

        foreach ($files as $file) {
            // Files starting with '@' should be treated as assets reference.
            if ($file[0] === '@') {
                $file = ltrim($file, '@');

                $collection[] = new AssetReference($this->assetManager, $file);
                continue;
            }

            // If file is not absolute path, prefix with assets base path.
            if ($this->basePath && !$this->isAbsolutePath($file)) {
                $file = $this->basePath . '/' . $file;
            }

            // Files with asterisks should be treated as glob.
            if (str_contains($file, '*')) {
                $collection[] = new GlobAsset($file);
                continue;
            }

            $collection[] = new FileAsset($file);
        }

        return $collection;
    }

    /**
     * Determine if the given file path is an absolute path.
     *
     * Note: Adapted from symfony\filesystem.
     *
     * @see https://github.com/symfony/symfony/blob/v3.2.2/LICENSE
     *
     * @param  string $file A file path.
     * @return boolean Returns TRUE if the given path is absolute. Otherwise, returns FALSE.
     */
    private function isAbsolutePath($file): bool
    {
        $file = (string)$file;

        return strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3
                && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && strspn($file, '/\\', 2, 1))
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}
