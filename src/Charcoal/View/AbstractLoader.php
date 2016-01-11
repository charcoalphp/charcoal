<?php

namespace Charcoal\View;

// PHP Dependencies
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

/**
 *
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
    public function __construct(array $data)
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
            $this->set_default_paths();
        }

        return $this->paths;
    }

    /**
     * @return AbstractLoader Chainable
     */
    public function set_default_paths()
    {
        $paths = [];

        // Use default templates path (from app config) if none was set
        if (class_exists('\Charcoal\App\App')) {
            $paths = \Charcoal\App\App::instance()->config()->get('view/path');
        }

        $this->set_paths($paths);

        return $this;
    }

    /**
     * @param string[] $paths The list of path to add.
     * @return AbstractLoader Chainable
     */
    public function set_paths(array $paths)
    {
        $this->paths = [];

        foreach ($paths as $path) {
            $this->add_path($path);
        }

        return $this;
    }

    /**
     * @param string $path The path to add to the load.
     * @return AbstractLoader Chainable
     */
    public function add_path($path)
    {
        $this->paths[] = $this->resolve_path($path);

        return $this;
    }

    /**
     * @param string $path The path to add (prepend) to the load.
     * @return AbstractLoader Chainable
     */
    public function prepend_path($path)
    {
        $path = $this->resolve_path($path);
        array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * @param string $path The path to resolve.
     * @throws InvalidArgumentException If the path argument is not a string.
     * @return string
     */
    public function resolve_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $path = rtrim($path, '/\\').DIRECTORY_SEPARATOR;

        if (class_exists('\Charcoal\App\App')) {
            $base_path = \Charcoal\App\App::instance()->config()->get('ROOT');
            $base_path = rtrim($base_path, '/\\').DIRECTORY_SEPARATOR;
            if (false === strpos($path, $base_path)) {
                $path = $base_path.$path;
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
