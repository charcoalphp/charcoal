<?php

namespace Charcoal\View;

// PHP Dependencies
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

/**
 * Base template loader.
 */
abstract class AbstractLoader implements
    LoggerAwareInterface,
    LoaderInterface
{
    use LoggerAwareTrait;

    /**
     * @var string $path
     */
    private $paths = [];

    /**
     * Default constructor, if none is provided by the concrete class implementations.
     *
     * ## Required dependencies
     * - `logger` A PSR-3 logger
     *
     * @param array $data The class dependencies map.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['logger'])) {
            $this->setLogger($data['logger']);
        }
    }

    /**
     * @see FileLoader::path()
     * @return string[]
     */
    public function paths()
    {
        if (empty($this->paths)) {
            $this->setDefaultPaths();
        }

        return $this->paths;
    }

    /**
     * @return AbstractLoader Chainable
     */
    public function setDefaultPaths()
    {
        $paths = [];

        // Use default templates path (from app config) if none was set
        if (class_exists('\Charcoal\App\App')) {
            $paths = \Charcoal\App\App::instance()->config()->get('view/path');
        }

        $this->setPaths($paths);

        return $this;
    }

    /**
     * @param string[] $paths The list of path to add.
     * @return AbstractLoader Chainable
     */
    public function setPaths(array $paths)
    {
        $this->paths = [];

        foreach ($paths as $path) {
            $this->addPath($path);
        }

        return $this;
    }

    /**
     * @param string $path The path to add to the load.
     * @return AbstractLoader Chainable
     */
    public function addPath($path)
    {
        $this->paths[] = $this->resolvePath($path);

        return $this;
    }

    /**
     * @param string $path The path to add (prepend) to the load.
     * @return AbstractLoader Chainable
     */
    public function prependPath($path)
    {
        $path = $this->resolvePath($path);
        array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * @param string $path The path to resolve.
     * @throws InvalidArgumentException If the path argument is not a string.
     * @return string
     */
    public function resolvePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $path = rtrim($path, '/\\').DIRECTORY_SEPARATOR;

        if (class_exists('\Charcoal\App\App')) {
            $basePath = \Charcoal\App\App::instance()->config()->get('ROOT');
            $basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;
            if (false === strpos($path, $basePath)) {
                $path = $basePath.$path;
            }
        }

        return $path;
    }

    /**
     * @param string $ident
     * @return string
     */
    abstract public function load($ident);
}
