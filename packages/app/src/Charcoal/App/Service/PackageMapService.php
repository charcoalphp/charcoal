<?php

namespace Charcoal\App\Service;

use UnexpectedValueException;

/**
 * Class PackageMapService
 */
class PackageMapService
{
    const PACKAGES_DIRECTORY = '/vendor/charcoal/charcoal/packages/';
    const CHARCOAL_PACKAGE = 'charcoal/charcoal';

    private bool $isMonoRepo;
    private string $basePath;

    /**
     * @param array $data The init data.
     */
    public function __construct(array $data)
    {
        $this->isMonoRepo = \Composer\InstalledVersions::isInstalled(self::CHARCOAL_PACKAGE);
        $this->basePath = $data['basePath'];
    }

    /**
     * @param string|string[] $value The value(s) to map to package.
     * @return mixed
     */
    public function map(&$value)
    {
        if (is_array($value)) {
            array_walk($value, [$this, 'mapOne']);
            return $value;
        }

        return $this->mapOne($value);
    }

    /**
     * Replaces placeholders (%package.key.path%) by their values in the config.
     *
     * @param  string $value A value to resolve.
     * @throws UnexpectedValueException If the resolved value is not a string or number.
     * @return mixed
     */
    public function mapOne(string &$value): string
    {
        $value = preg_replace_callback('/%%|%package\.([^\.%\s]+)\.path%/', function ($match) use ($value) {
            // skip escaped %%
            if (!isset($match[1])) {
                return '%%';
            }

            $package = $match[1];

            $resolved = ($this->resolvePackagePath($package) ?? null);

            if (!is_string($resolved) && !is_numeric($resolved)) {
                $resolvedType = (is_object($resolved) ? get_class($resolved) : gettype($resolved));

                throw new UnexpectedValueException(sprintf(
                    'Invalid config parameter "%s" inside string value "%s"; '.
                    'must be a string or number, received %s',
                    $package,
                    $value,
                    $resolvedType
                ));
            }

            return $resolved;
        }, $value);

        return $value;
    }

    /**
     * @param string $package The package string identifier.
     * @return string|null
     */
    private function resolvePackagePath(string $package): ?string
    {
        if ($this->isMonoRepo) {
            $directory = self::PACKAGES_DIRECTORY.$package;

            if (file_exists($this->basePath.$directory)) {
                return $directory;
            }
        } else {
            $packageName = 'charcoal/'.$package;

            if (\Composer\InstalledVersions::isInstalled($packageName)) {
                return \Composer\InstalledVersions::getInstallPath($packageName);
            };
        }

        return null;
    }
}
