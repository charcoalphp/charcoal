<?php

namespace Charcoal\Tests\Ui;

use Charcoal\App\AppConfig;
use PDO;
use Psr\Log\NullLogger;
use Stash\Pool;
use DI\Container;
use Charcoal\Model\ServiceProvider\ModelServiceProvider;
use Charcoal\User\ServiceProvider\AuthServiceProvider;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\View\ViewServiceProvider;
use GuzzleHttp\Psr7\Uri;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;

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
        $this->registerBaseUrl($container);
        $this->registerTranslator($container);
        $this->registerDebug($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerConfig(Container $container)
    {
        $container->set('config', new AppConfig([
            'base_path'  => realpath(__DIR__ . '/../../..'),
            'locales'    => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
            'translator' => [
                'paths' => [],
            ],
        ]));

        /**
         * List of Charcoal module classes.
         *
         * Explicitly defined in case of a version mismatch with dependencies. This parameter
         * is normally defined by {@see \Charcoal\App\ServiceProvider\AppServiceProvider}.
         *
         * @var array
         */
        $container->set('module/classes', []);
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
        $container->set('database', function () {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        });
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
        $container->set('logger', function () {
            return new NullLogger();
        });
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerCache(Container $container)
    {
        $container->set('cache', function () {
            return new Pool();
        });
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseUrl(Container $container)
    {
        $container->set('base-url', function () {
            return (new Uri(''));
        });
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslator(Container $container)
    {
        $container->set('locales/manager', function (Container $container) {
            return new LocalesManager([
                'locales' => [
                    'en' => [ 'locale' => 'en-US' ]
                ]
            ]);
        });

        $container->set('translator', function (Container $container) {
            return new Translator([
                'manager' => $container->get('locales/manager')
            ]);
        });
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDebug(Container $container)
    {
        if (!($container->has('debug'))) {
            $container->set('debug', false);
        }
    }
}
