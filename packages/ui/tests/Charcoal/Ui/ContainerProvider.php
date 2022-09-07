<?php

namespace Charcoal\Tests\Ui;

use Charcoal\App\AppConfig;
use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From Pimple
use Pimple\Container;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Model\ServiceProvider\ModelServiceProvider;

// From 'charcoal-user'
use Charcoal\User\ServiceProvider\AuthServiceProvider;

// From 'charcoal-translator'
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;

// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;

/**
 * Service Container for Unit Tests
 */
class ContainerProvider
{
    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseServices(Container $container)
    {
        $this->registerConfig($container);
        $this->registerSource($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerConfig(Container $container)
    {
        $container['config'] = new AppConfig([
            'base_path'  => realpath(__DIR__.'/../../..'),
            'locales'    => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
            'translator' => [
                'paths' => [],
            ],
        ]);

        /**
         * List of Charcoal module classes.
         *
         * Explicitly defined in case of a version mismatch with dependencies. This parameter
         * is normally defined by {@see \Charcoal\App\ServiceProvider\AppServiceProvider}.
         *
         * @var array
         */
        $container['module/classes'] = [];
    }

    /**
     * Setup the application's data source interface.
     *
     * Note: Uses SQLite to create a database in memory.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerSource(Container $container)
    {
        $container['database'] = function () {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerModelServices(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ModelServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAuthServices(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new AuthServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslatorServices(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new TranslatorServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * Setup the framework's view renderer.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerViewServices(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ViewServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * Setup the application's logging interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerLogger(Container $container)
    {
        $container['logger'] = function () {
            return new NullLogger();
        };
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerCache(Container $container)
    {
        $container['cache'] = function () {
            return new Pool();
        };
    }
}
