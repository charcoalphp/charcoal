<?php

namespace Charcoal\View;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;

// Local namespace dependencies
use \Charcoal\View\EngineInterface;
use \Charcoal\View\LoaderInterface;

/**
* Default implementation, as abstract class, of the `EngineInterface`.
*
* View Engines are comprised of 2 things:
* - A template loader, wich is a `LoaderInterfaceObject`
*   - Set with `set_loader()` / Get with `loader()`
*   - Provides `loadtemplate()` method
* - A `render()` method, which takes a $template and a $context arguments
*
* > Engines implements the `LoggerAwareInterface`. A logger can be accessed with the `logger()` method.
*/
abstract class AbstractEngine implements
    EngineInterface,
    LoggerAwareInterface
{
    /**
    * @var LoaderInterface $loader
    */
    private $loader;

    /**
    * @var LoggerInterface $logger
    */
    private $logger;

    /**
    * @return string
    */
    abstract public function type();

    /**
    * > LoggerAwareInterface > setLogger()
    *
    * Fulfills the PSR-3 style LoggerAwareInterface `setLogger`
    *
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
    public function set_logger(LoggerInterface $logger)
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
    * @param LoaderInterface $loader
    * @return MustacheEngine Chainable
    */
    public function set_loader(LoaderInterface $loader)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
    * @return LoaderInterface
    */
    public function loader()
    {
        if ($this->loader === null) {
            $this->loader = $this->create_loader();
        }
        return $this->loader;
    }

    /**
    * @return LoaderInterface
    */
    abstract public function create_loader();

    /**
    * Delegates template loading to the engine's Loader object.
    *
    * @param string $template_ident
    * @return string
    */
    public function load_template($template_ident)
    {
        return $this->loader()->load($template_ident);
    }
}
