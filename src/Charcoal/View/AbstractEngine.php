<?php

namespace Charcoal\View;

// PSR-3 logger
use \Psr\Log\LoggerInterface;
use \Psr\Log\LoggerAwareInterface;


// Local namespace dependencies
use \Charcoal\View\EngineInterface;
use \Charcoal\View\LoaderInterface;

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
    * Fulfills the PSR-1 style LoggerAwareInterface
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
    * @erturn LoggerInterface
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
    * @param string $template_ident
    * @return string
    */
    public function load_template($template_ident)
    {
        return $this->loader()->load($template_ident);
    }
}
