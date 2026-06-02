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
    private ?array $paths = null;

    /**
     * Mapping of domains/locales/messages.
     */
    private ?array $translations = null;

    /**
     * Debug mode.
     */
    private ?bool $debug = null;

    /**
     * The directory to use for the cache.
     */
    private ?string $cacheDir = null;

    #[\Override]
    public function defaults(): array
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
    public function setLoaders(array $loaders): static
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
    public function setPaths(array $paths): static
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
    public function addPaths(array $paths): static
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
    public function paths(): ?array
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
    public function setTranslations(array $translations): static
    {
        $this->translations = [];
        foreach ($translations as $data) {
            if (!is_array($data)) {
                throw new InvalidArgumentException(
                    'Translator translations must be a 3-level array'
                );
            }

            foreach ($data as $messages) {
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
    public function translations(): ?array
    {
        return $this->translations;
    }

    /**
     * @param boolean $debug The debug flag.
     * @return TranslatorConfig Chainable
     */
    public function setDebug($debug): static
    {
        $this->debug = (bool)$debug;
        return $this;
    }

    /**
     * @return boolean
     */
    public function debug(): ?bool
    {
        return $this->debug;
    }

    /**
     * @param  string $cacheDir The cache directory.
     * @throws InvalidArgumentException If the cache dir argument is not a string.
     * @return TranslatorConfig Chainable
     */
    public function setCacheDir($cacheDir): static
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
    public function cacheDir(): ?string
    {
        return $this->cacheDir;
    }

    private function availableLoaders(): array
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
