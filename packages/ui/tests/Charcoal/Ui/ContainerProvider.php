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
     */
    public function registerBaseServices(Container $container): void
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
     */
    public function registerConfig(Container $container): void
    {
        $container['config'] = new AppConfig([
            'base_path'  => realpath(__DIR__ . '/../../..'),
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
         */
        $container['module/classes'] = [];
    }

    /**
     * Setup the application's data source interface.
     *
     * Note: Uses SQLite to create a database in memory.
     *
     * @param  Container $container A DI container.
     */
    public function registerSource(Container $container): void
    {
        $container['database'] = function (): \PDO {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     */
    public function registerModelServices(Container $container): void
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
     */
    public function registerAuthServices(Container $container): void
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
     */
    public function registerTranslatorServices(Container $container): void
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
     */
    public function registerViewServices(Container $container): void
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
     */
    public function registerLogger(Container $container): void
    {
        $container['logger'] = (fn(): \Psr\Log\NullLogger => new NullLogger());
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerCache(Container $container): void
    {
        $container['cache'] = (fn(): \Stash\Pool => new Pool());
    }
}
