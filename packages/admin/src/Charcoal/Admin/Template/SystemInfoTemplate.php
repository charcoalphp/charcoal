<?php

namespace Charcoal\Admin\Template;

use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\User;
use Charcoal\Loader\CollectionLoaderAwareTrait;
use Composer\InstalledVersions;
use Psr\Container\ContainerInterface;
use DI\Container;
use Charcoal\Source\DatabaseSource;
use PDO;
use Charcoal\Image\ImageFactory;
use Charcoal\Image\Imagick\ImagickImage;

/**
 * Admin System Info template
 */
class SystemInfoTemplate extends AdminTemplate
{
    use CollectionLoaderAwareTrait;

    /**
     * Set common dependencies (services) used in all admin templates.
     *
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        $this->setCollectionLoader($container->get('model/collection/loader'));
    }

    protected function authRequired()
    {
        return true;
    }

    public function phpInfo()
    {
        // Remove any arrays from $_ENV and $_SERVER to get around an "Array to string conversion" error
        $envVals = [];
        $serverVals = [];

        foreach ($_ENV as $key => $value) {
            if (is_array($value)) {
                $envVals[$key] = $value;
                $_ENV[$key] = 'Array';
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (is_array($value)) {
                $serverVals[$key] = $value;
                $_SERVER[$key] = 'Array';
            }
        }

        ob_start();
        phpinfo(INFO_ALL);
        $phpInfoStr = ob_get_clean();

        // Put the original $_ENV and $_SERVER values back
        foreach ($envVals as $key => $value) {
            $_ENV[$key] = $value;
        }
        foreach ($serverVals as $key => $value) {
            $_SERVER[$key] = $value;
        }

        $replacePairs = [
            '#^.*<body>(.*)</body>.*$#ms' => '$1',
            '#<h2>PHP License</h2>.*$#ms' => '',
            '#<h1>Configuration</h1>#' => '',
            "#\r?\n#" => '',
            '#</(h1|h2|h3|tr)>#' => '</$1>' . "\n",
            '# +<#' => '<',
            "#[ \t]+#" => ' ',
            '#&nbsp;#' => ' ',
            '#  +#' => ' ',
            '# class=".*?"#' => '',
            '%&#039;%' => ' ',
            '#<tr>(?:.*?)"src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#' => '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' . "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
            '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#' => '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
            '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#' => '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" . '<tr><td>Zend Egg</td><td>$1</td></tr>',
            '# +#' => ' ',
            '#<tr>#' => '%S%',
            '#</tr>#' => '%E%',
        ];

        $phpInfoStr = preg_replace(array_keys($replacePairs), array_values($replacePairs), $phpInfoStr);

        $sections = explode('<h2>', strip_tags($phpInfoStr, '<h2><th><td>'));
        unset($sections[0]);

        $phpInfo = [];

        foreach ($sections as $section) {
            $heading = substr($section, 0, strpos($section, '</h2>'));

            if (preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $section, $matches, PREG_SET_ORDER) !== 0) {
                /** @var array[] $matches */
                foreach ($matches as $row) {
                    if (!isset($row[2])) {
                        continue;
                    }

                    $value = $row[2];
                    $name = $row[1];

                    $phpInfo[$heading][$name] = $value;
                }
            }
        }

        return $this->formatTable($phpInfo);
    }

    public function formatTable(array $data = []): array
    {
        $formattedData = [];

        foreach ($data as $key => $value) {
            $formattedData[] = [
                'label' => $key,
                'value' => is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value,
            ];
        }

        return ['tableData' => $formattedData];
    }

    public function systemInfo()
    {
        $extensions = [
            'mbstring'  => extension_loaded('mbstring'),
            'openssl'   => extension_loaded('openssl'),
            'gd'        => extension_loaded('gd'),
            'imagick'   => extension_loaded('imagick'),
            'curl'      => extension_loaded('curl'),
            'fileinfo'  => extension_loaded('fileinfo'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml'       => extension_loaded('xml'),
            'zip'       => extension_loaded('zip'),
            'json'      => extension_loaded('json'),
            'pdo'       => extension_loaded('pdo'),
        ];

        $pdoDrivers = [];
        if ($extensions['pdo']) {
            try {
                $pdoDrivers = \PDO::getAvailableDrivers();
            } catch (\Throwable $e) {
                $pdoDrivers = ['error' => $e->getMessage()];
            }
        }
        $extensions['pdo_drivers'] = $pdoDrivers;

        $data = [
            'Charcoal version'          => (InstalledVersions::getPrettyVersion('charcoal/charcoal') ?? InstalledVersions::getVersion('charcoal/charcoal')),
            'PHP version'               => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION,
            'PHP server api'            => PHP_SAPI,
            'Web server'                => ($_SERVER['SERVER_SOFTWARE'] ?? null),
            'OS version'                => PHP_OS . ' ' . php_uname('r'),
            'Database driver & version' => $this->getDatabaseDriver(),
            'Twig version'              => (InstalledVersions::getPrettyVersion('twig/twig') ?? InstalledVersions::getVersion('twig/twig')),
            'Mustache version'          => (InstalledVersions::getPrettyVersion('mustache/mustache') ?? InstalledVersions::getVersion('mustache/mustache')),
            'Image driver & version'    => self::getImageDriver(),
            'Project directory'         => getcwd(),
            'Timezone'                  => date_default_timezone_get(),
            'memory_limit'              => ini_get('memory_limit'),
            'max_execution_time'        => ini_get('max_execution_time'),
            'upload_max_filesize'       => ini_get('upload_max_filesize'),
            'post_max_size'             => ini_get('post_max_size'),
            'Extensions'                => $extensions,
        ];

        $systemInfo = $this->formatTable($data);

        return $systemInfo;
    }

    /**
     * Returns the image driver name and version
     *
     * @return string
     */
    private static function getImageDriver(): string
    {
        $factory = new ImageFactory();
        try {
            /** @var ImagickImage $image */
            $image = $factory->create('imagick');
            $imagickVersion = 'Imagick ' . phpversion('imagick');
            $driverVersion = ($image->imagick()->getVersion()['versionString'] ?? '');
            $driverVersion = "$imagickVersion ($driverVersion)";
        } catch (\Throwable $th) {
            $driverVersion = 'Imagick does not appear to be installed';
        }

        return $driverVersion;
    }

    private function getDatabaseDriver()
    {
        /** @var DatabaseSource $source */
        $source = $this->collectionLoader()->setModel(User::class)->source();
        $pdo = $source->db();
        $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (str_contains(strtolower($serverVersion), 'mariadb')) {
            $driverName = 'MariaDB';
        }

        $serverVersion = self::normalizeVersion($serverVersion);

        return "$driverName $serverVersion";
    }

    /**
     * Removes distribution info from a version string, and returns the highest version number found in the remainder.
     *
     * @param string $version
     * @return string
     */
    private static function normalizeVersion(string $version): string
    {
        // Strip out the distribution info
        $versionPattern = '\d[\d.]*(-(dev|alpha|beta|rc)(\.?\d[\d.]*)?)?';
        if (!preg_match("/^((v|version\s*)?$versionPattern-?)+/i", $version, $match)) {
            return '';
        }
        $version = $match[0];

        // Return the highest version
        preg_match_all("/$versionPattern/i", $version, $matches, PREG_SET_ORDER);
        $versions = array_map(fn(array $match) => $match[0], $matches);
        usort($versions, fn($a, $b) => match (true) {
            version_compare($a, $b, '<') => 1,
            version_compare($a, $b, '>') => -1,
            default => 0,
        });
        return reset($versions) ?: '';
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('System Information'));
        }

        return $this->title;
    }
}
