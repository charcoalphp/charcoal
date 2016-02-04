<?php

namespace Charcoal\View;

// PHP Dependencies
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// Local namespace dependencies
use \Charcoal\View\LoaderInterface;

/**
 * Base template loader.
 */
abstract class AbstractLoader implements
    LoggerAwareInterface,
    LoaderInterface
{
    use LoggerAwareTrait;

    /**
     * @var string $basePath
     */
    private $basePath = '';

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

        if (isset($data['base_path'])) {
            $this->setBasePath($data['base_path']);
        } else {
            if (class_exists('\Charcoal\App\App')) {
                $this->logger->debug('OBSOLETE: Using Charcoal App  for base path (1)');
                $basePath = \Charcoal\App\App::instance()->config()->get('ROOT');
                $basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;
                $this->setBasePath($basePath);
            }
        }

        if (isset($data['paths'])) {
            $this->setPaths($data['paths']);
        } else {
            if (class_exists('\Charcoal\App\App')) {
                $this->logger->debug('OBSOLETE: Using Charcoal App  for paths (2)');
                $this->setPaths(\Charcoal\App\App::instance()->config()->get('view.path'));
            }
        }
    }

    /**
     * @param string $basePath The base path to set.
     * @throws InvalidArgumentException if the base path parameter is not a string.
     * @return LoaderInterface Chainable
     */
    public function setBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new InvalidArgumentException(
                'Base path must be a string'
            );
        }
        $basePath = realpath($basePath);
        $this->basePath = rtrim($basePath, '/\\').DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * @see FileLoader::path()
     * @return string[]
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * @param string[] $paths The list of path to add.
     * @return LoaderInterface Chainable
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
     * @return LoaderInterface Chainable
     */
    public function addPath($path)
    {
        $this->paths[] = $this->resolvePath($path);

        return $this;
    }

    /**
     * @param string $path The path to add (prepend) to the load.
     * @return LoaderInterface Chainable
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

        $basePath = $this->basePath();
        $path = rtrim($path, '/\\').DIRECTORY_SEPARATOR;
        if ($basePath && strpos($path, $basePath) === false) {
            $path = $basePath.$path;
        }

        return $path;
    }

    /**
     * @param string $ident
     * @return string
     */
    abstract public function load($ident);
}
