<?php

declare(strict_types=1);

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
    public function defaults(): array
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
     * @return self
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];
        $this->addPaths($paths);
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
     * @return self
     */
    public function addPath(string $path)
    {
        $this->paths[] = $path;
        return $this;
    }

    /**
     * @return array
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $engines The various engines configuration.
     * @return self
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
     * @return self
     */
    public function addEngine(string $engineIdent, array $engineConfig)
    {
        $this->engines[$engineIdent] = $engineConfig;
        return $this;
    }

    /**
     * @return array
     */
    public function engines(): array
    {
        return $this->engines;
    }

    /**
     * Get an engine's configuration.
     *
     * @param string|null $engineIdent The engine identifier to get the configuration of.
     * @throws InvalidArgumentException If the engine ident does not match any engines.
     * @return array
     */
    public function engine(?string $engineIdent = null): array
    {
        if ($engineIdent === null) {
            $engineIdent = $this->defaultEngine();
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
     * @return self
     */
    public function setDefaultEngine(string $engineIdent)
    {
        $this->defaultEngine = $engineIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function defaultEngine(): string
    {
        return $this->defaultEngine;
    }
}
