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
    private array $paths = [];

    private array $engines = [];

    private ?string $defaultEngine = null;

    #[\Override]
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
     */
    public function setPaths(array $paths): static
    {
        $this->paths = [];
        $this->addPaths($paths);
        return $this;
    }

    /**
     * @param  string[] $paths One or more search paths.
     */
    public function addPaths(array $paths): static
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
        return $this;
    }

    /**
     * @param string $path A path to add to the paths list.
     * @throws InvalidArgumentException If the path is not a string.
     */
    public function addPath(string $path): static
    {
        $this->paths[] = $path;
        return $this;
    }

    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * @param array $engines The various engines configuration.
     */
    public function setEngines(array $engines): static
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
     */
    public function addEngine(string $engineIdent, array $engineConfig): static
    {
        $this->engines[$engineIdent] = $engineConfig;
        return $this;
    }

    public function engines(): array
    {
        return $this->engines;
    }

    /**
     * Get an engine's configuration.
     *
     * @param string|null $engineIdent The engine identifier to get the configuration of.
     * @throws InvalidArgumentException If the engine ident does not match any engines.
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
     */
    public function setDefaultEngine(string $engineIdent): static
    {
        $this->defaultEngine = $engineIdent;
        return $this;
    }

    public function defaultEngine(): string
    {
        return $this->defaultEngine;
    }
}
