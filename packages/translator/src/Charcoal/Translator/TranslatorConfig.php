<?php

namespace Charcoal\Translator;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Translator Configset
 *
 * Stores the translator's settings, catalogs to be loaded,
 * catalog loaders, and extra translations.
 */
class TranslatorConfig extends AbstractConfig
{
    /**
     * Available resource loaders.
     *
     * @var string[]
     */
    private $loaders;

    /**
     * Translation resource paths.
     *
     * @var string[]
     */
    private $paths;

    /**
     * Mapping of domains/locales/messages.
     *
     * @var array
     */
    private $translations;

    /**
     * Debug mode.
     *
     * @var boolean
     */
    private $debug;

    /**
     * The directory to use for the cache.
     *
     * @var string
     */
    private $cacheDir;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'loaders' => [
                'csv',
            ],
            'paths' => [
                'translations/',
            ],
            'translations' => [],
            'debug'        => false,
            'cache_dir'    => '../cache/translator',
        ];
    }

    /**
     * @param  string[] $loaders The list of active loaders.
     * @throws InvalidArgumentException If the loader is invalid.
     * @return TranslatorConfig Chainable
     */
    public function setLoaders(array $loaders)
    {
        $this->loaders = [];
        foreach ($loaders as $loader) {
            if (!in_array($loader, $this->availableLoaders())) {
                throw new InvalidArgumentException(sprintf(
                    'Loader "%s" is not a valid loader.',
                    $loader
                ));
            }
            $this->loaders[] = $loader;
        }
        return $this;
    }

    /**
     * @return string[]
     */
    public function loaders()
    {
        return $this->loaders;
    }

    /**
     * @param  string[] $paths The "paths" (search pattern) to look into for translation resources.
     * @return TranslatorConfig Chainable
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);
        return $this;
    }

    /**
     * @param  string[] $paths The "paths" (search pattern) to look into for translation resources.
     * @throws InvalidArgumentException If the path is not a string.
     * @return TranslatorConfig Chainable
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            if (!is_string($path)) {
                throw new InvalidArgumentException(
                    'Translator path must be a string'
                );
            }
            $this->paths[] = $path;
        }
        return $this;
    }

    /**
     * @return string[]
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * Set mapping of additional translations.
     *
     * Expects:
     * ```json
     * {
     *     "<domain>": {
     *        "<locale>": {
     *            "<translation-key>": "translation"
     *        }
     *     }
     * }
     * ```
     *
     * @param  array $translations Mapping of domains/locales/messages.
     * @throws InvalidArgumentException If the path is not a string.
     * @return TranslatorConfig Chainable
     */
    public function setTranslations(array $translations)
    {
        $this->translations = [];
        foreach ($translations as $domain => $data) {
            if (!is_array($data)) {
                throw new InvalidArgumentException(
                    'Translator translations must be a 3-level array'
                );
            }

            foreach ($data as $locale => $messages) {
                if (!is_array($messages)) {
                    throw new InvalidArgumentException(
                        'Translator translations must be a 3-level array'
                    );
                }
            }
        }

        $this->translations = $translations;

        return $this;
    }

    /**
     * Retrieve mapping of additional translations.
     *
     * @return array
     */
    public function translations()
    {
        return $this->translations;
    }

    /**
     * @param boolean $debug The debug flag.
     * @return TranslatorConfig Chainable
     */
    public function setDebug($debug)
    {
        $this->debug = !!$debug;
        return $this;
    }

    /**
     * @return boolean
     */
    public function debug()
    {
        return $this->debug;
    }

    /**
     * @param  string $cacheDir The cache directory.
     * @throws InvalidArgumentException If the cache dir argument is not a string.
     * @return TranslatorConfig Chainable
     */
    public function setCacheDir($cacheDir)
    {
        if (!is_string($cacheDir)) {
            throw new InvalidArgumentException(
                'Cache dir must be a string'
            );
        }
        $this->cacheDir = $cacheDir;
        return $this;
    }

    /**
     * @return string
     */
    public function cacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return array
     */
    private function availableLoaders()
    {
        return [
            'csv',
            'dat',
            'res',
            'ini',
            'json',
            'mo',
            'php',
            'po',
            'qt',
            'xliff',
            'yaml',
        ];
    }
}
