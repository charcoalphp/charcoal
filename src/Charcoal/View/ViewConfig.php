<?php

namespace Charcoal\View;

use InvalidArgumentException;

// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

// From 'charcoal-view'
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Twig\TwigEngine;

/**
 * View configuration.
 */
class ViewConfig extends AbstractConfig
{
    /**
     * @var array $paths
     */
    private $paths = [];

    /**
     * @var array $engines
     */
    private $engines = [];

    /**
     * @var string $defaultEngine
     */
    private $defaultEngine;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'paths' => [],
            'engines' => [
                'mustache'      => [
                    'cache' => MustacheEngine::DEFAULT_CACHE_PATH
                ],
                'php'           => [],
                'php-mustache'  => [],
                'twig'          => [
                    'cache' => TwigEngine::DEFAULT_CACHE_PATH
                ]
            ],
            'default_engine' => 'mustache'
        ];
    }

    /**
     * @param array $paths The paths to search into.
     * @return ViewConfig Chainable
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];
        foreach ($paths as $p) {
            $this->addPath($p);
        }
        return $this;
    }

    /**
     * @param  string[] $paths One or more search paths.
     * @return self
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
        return $this;
    }

    /**
     * @param string $path A path to add to the paths list.
     * @throws InvalidArgumentException If the path is not a string.
     * @return ViewConfig Chainable
     */
    public function addPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Template path must be a string'
            );
        }
        $this->paths[] = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * @param array $engines The various engines configuration.
     * @return ViewConfig Chainable
     */
    public function setEngines(array $engines)
    {
        $this->engines = [];
        foreach ($engines as $engineIdent => $engineConfig) {
            $this->addEngine($engineIdent, $engineConfig);
        }
        return $this;
    }

    /**
     * @param string $engineIdent  The engine identifier.
     * @param array  $engineConfig The engine configuration data.
     * @throws InvalidArgumentException If the engine ident is not a string.
     * @return ViewConfig Chainable
     */
    public function addEngine($engineIdent, array $engineConfig)
    {
        if (!is_string($engineIdent)) {
            throw new InvalidArgumentException(
                'Can not add engine to view config: engine identifier must be a string.'
            );
        }
        $this->engines[$engineIdent] = $engineConfig;
        return $this;
    }

    /**
     * @return array
     */
    public function engines()
    {
        return $this->engines;
    }

    /**
     * Get an engine's configuration.
     *
     * @param string|null $engineIdent The engine identifier to get the configuration of.
     * @throws InvalidArgumentException If the engine ident is not a string or does not match any engines.
     * @return array
     */
    public function engine($engineIdent = null)
    {
        if ($engineIdent === null) {
            $engineIdent = $this->defaultEngine();
        }
        if (!is_string($engineIdent)) {
            throw new InvalidArgumentException(
                'Invalid engine identifier (must be a string)'
            );
        }

        if (!isset($this->engines[$engineIdent])) {
            throw new InvalidArgumentException(
                sprintf('No configured engines matching "%s"', $engineIdent)
            );
        }
        return $this->engines[$engineIdent];
    }

    /**
     * @param string $engineIdent The default engine (identifier).
     * @throws InvalidArgumentException If the engine ident is not a string.
     * @return ViewConfig Chainable
     */
    public function setDefaultEngine($engineIdent)
    {
        if (!is_string($engineIdent)) {
            throw new InvalidArgumentException(
                'Default engine must be a string'
            );
        }
        $this->defaultEngine = $engineIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function defaultEngine()
    {
        return $this->defaultEngine;
    }
}
