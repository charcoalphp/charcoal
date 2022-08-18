<?php

namespace Charcoal\Tests;

use PDO;

// From Mockery
use Mockery;

// From Pimple
use Pimple\Container;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From Slim
use Slim\Http\Uri;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Model\ServiceProvider\ModelServiceProvider;

// From 'charcoal-user'
use Charcoal\User\ServiceProvider\AuthServiceProvider;

// From 'charcoal-translator'
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;

// From 'charcoal-view'
use Charcoal\View\ViewServiceProvider;

// From 'charcoal-app'
use Charcoal\App\AppConfig;

// From 'charcoal-admin'
use Charcoal\Admin\ServiceProvider\AdminServiceProvider;
use Charcoal\Admin\Config as AdminConfig;

/**
 *
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
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Register the admin services.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAdminServices(Container $container)
    {
        $this->registerBaseUrl($container);
        $this->registerAdminConfig($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerBaseUrl(Container $container)
    {
        $container['base-url'] = function () {
            return Uri::createFromString('');
        };

        $container['admin/base-url'] = function () {
            return Uri::createFromString('admin');
        };
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerConfig(Container $container)
    {
        $container['config'] = function () {
            return new AppConfig([
                'base_path'  => realpath(__DIR__.'/../../..'),
                'apis'       => [
                    'google' => [
                        'recaptcha' => [
                            'public_key'  => 'foobar',
                            'private_key' => 'bazqux',
                        ],
                    ],
                ],
                'locales'    => [
                    'en' => [
                        'locale' => 'en-US',
                    ],
                ],
                'translator' => [
                    'paths' => [],
                ],
                'metadata'   => [
                    'paths'  => [
                        'metadata',
                        // Standalone
                        'vendor/charcoal/object/metadata',
                        'vendor/charcoal/user/metadata',
                        // Monorepo
                        '/../object/metadata',
                        '/../user/metadata',
                    ],
                ],
            ]);
        };

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
     * Setup the admin module configset.
     *
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAdminConfig(Container $container)
    {
        $this->registerConfig($container);

        $container['admin/config'] = function () {
            return new AdminConfig();
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerClimate(Container $container)
    {
        $container['climate/system'] = function () {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };

        $container['climate/output'] = function () {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };

        $container['climate/reader'] = function () {
            $reader = Mockery::mock(Stdin::class);
            $reader->shouldReceive('line')->andReturn('line');
            $reader->shouldReceive('char')->andReturn('char');
            $reader->shouldReceive('multiLine')->andReturn('multiLine');
            return $reader;
        };

        $container['climate/util'] = function (Container $container) {
            return new UtilFactory($container['climate/system']);
        };

        $container['climate'] = function (Container $container) {
            $climate = new CLImate();

            $climate->setOutput($container['climate/output']);
            $climate->setUtil($container['climate/util']);
            $climate->setReader($container['climate/reader']);

            return $climate;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerDatabase(Container $container)
    {
        $container['database'] = function () {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerModelServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ModelServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAuthServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new AuthServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerTranslatorServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new TranslatorServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerViewServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new ViewServiceProvider();
        }

        $provider->register($container);
    }

    /**
     * @param  Container $container A DI container.
     * @return void
     */
    public function registerAdminServiceProvider(Container $container)
    {
        static $provider = null;

        if ($provider === null) {
            $provider = new AdminServiceProvider();
        }

        $provider->register($container);
    }

    /**
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
