<?php

namespace Charcoal\View;

// PHP Dependencies
use \InvalidArgumentException;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

/**
 *
 */
abstract class AbstractLoader implements
    LoggerAwareInterface,
    LoaderInterface
{
    /**
     * @var string $path
     */
    private $paths = [];

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

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
        $this->set_logger($data['logger']);
    }

    /**
     * Fulfills the PSR-1 style (camelCaps) LoggerAwareInterface
     *
     * @see LoggerAwareInterface::setLogger()
     * @param LoggerInterface $logger
     * @return AbstractEngine Chainable
     */
    public function setLogger(LoggerInterface $logger)
    {
        return $this->set_logger($logger);
    }

    /**
     * @param LoggerInterface $logger
     * @return AbstractEngine Chainable
     */
    public function set_logger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
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
        if (class_exists('\Charcoal\Charcoal')) {
            $paths = $paths + \Charcoal\Charcoal::config()->templates_path();
        }

        if (class_exists('\Charcoal\App\App')) {
            $paths = $paths + \Charcoal\App\App::instance()->config()->get('view/path');
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
     * @throws InvalidArgumentException If the path  argument is not a string.
     * @return AbstractLoader Chainable
     */
    public function add_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $path = rtrim($path, '/\\').'/';
        $this->paths[] = $path;

        return $this;
    }

    /**
     * @param string $path The path to add (prepend) to the load.
     * @throws InvalidArgumentException If the path  argument is not a string.
     * @return AbstractLoader Chainable
     */
    public function prepend_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path needs to be a string'
            );
        }

        $path = rtrim($path, '/\\').'/';
        array_unshift($this->paths, $path);

        return $this;
    }

    /**
     * @param string $ident
     * @return string
     */
    abstract public function load($ident);
}
