<?php

namespace Charcoal;

// 3rd-party libraries dependencies from PSR-1 and PSR-7
use \Psr\Log\LogLevel;

// 3rd-party libraries dependencies, from Monolog
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Processor\UidProcessor;

/**
* The base class for the `admin` Module
*/
class CharcoalModule
{
    /**
    * @var CharcoalConfig $config
    */
    private $config;
    /**
    * @var \Slim\App $app
    */
    private $app;

    /**
    * Charcoal Module setup.
    *
    * Sets all that's needed for the base Charcoal utilities.
    *
    * Provides:
    * - The root configuration of the project
    */
    public static function setup($app)
    {
        $container = $app->getContainer();

        if (!isset($container['charcoal/module'])) {
            $container['charcoal/module'] = function ($c) use ($app) {
                return new CharcoalModule([
                    'config' => $c['charcoal/config'],
                    'app'    => $app
                ]);
            };
        }

        if (!isset($container['charcoal/config']) && isset($container['config'])) {
            $container['charcoal/config'] = function ($c) {
                return $c['config'];
            };
        }

        if (!isset($container['logger'])) {
            $container['logger'] = function ($c) {
                $logger    = new Logger('charcoal');
                $processor = new UidProcessor();
                $handler   = new StreamHandler('charcoal.debug.log', LogLevel::DEBUG);

                $logger->pushProcessor($processor);
                $logger->pushHandler($handler);

                return $logger;
            };
        }

        /** Setup and initialize Charcoal */
        Charcoal::init([
            'config' => $container['charcoal/config'],
            'logger' => $container['logger'],
            'app'    => $app
        ]);
    }

    public function __construct($data)
    {
        $this->config = $data['config'];
        $this->app    = $data['app'];
    }
}
