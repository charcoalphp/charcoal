<?php

namespace Charcoal\Tests\Translator;

use PDO;

// From Mockery
use Mockery;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash'
use Stash\Pool;
use Stash\Driver\Ephemeral;

// From Slim
use Slim\Http\Uri;

// From Pimple
use Pimple\Container;

// From 'symfony/translation'
use Symfony\Component\Translation\MessageSelector;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-app'
use Charcoal\App\AppConfig;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesConfig;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\TranslatorConfig;
use Charcoal\Translator\Translator;

/**
 *
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
        $this->registerBaseUrl($container);
        $this->registerSource($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     */
    public function registerModelServices(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerMetadataLoader($container);
        $this->registerPropertyFactory($container);
        $this->registerSourceFactory($container);
        $this->registerModelFactory($container);
    }

    /**
     * Register the unit tests required services.
     *
     * @param  Container $container A DI container.
     */
    public function registerAdminServices(Container $container): void
    {
        $this->registerClimate($container);
        $this->registerAdminBaseUrl($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     */
    public function registerBaseUrl(Container $container): void
    {
        $container['base-url'] = (fn() => Uri::createFromString('https://example.com:8080/foo/bar?abc=123'));
    }

    /**
     * Setup the admin's base URI.
     *
     * @param  Container $container A DI container.
     */
    public function registerAdminBaseUrl(Container $container): void
    {
        $container['admin/base-url'] = (fn() => Uri::createFromString('https://example.com:8080/admin/qux?abc=123'));
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     */
    public function registerConfig(Container $container): void
    {
        $container['config'] = (fn(): \Charcoal\App\AppConfig => new AppConfig([
            'base_path' => realpath(__DIR__ . '/../../..'),
            'locales'   => [
                'languages' => [
                    'en' => [ 'locale' => 'en-US', 'locales' => [ 'en_US.UTF-8', 'en_US.utf8', 'en_US' ] ],
                    'fr' => [ 'locale' => 'fr-FR' ]
                ],
                'default_language'   => 'en',
                'fallback_languages' => [ 'en' ]
            ],
            'translator' => [
                'paths' => [
                    '/Charcoal/Translator/Fixture/translations'
                ],
                'translations' => [
                    'messages' => [
                        'en' => [
                            'foo' => 'FOO'
                        ],
                        'fr' => [
                            'foo' => 'OOF'
                        ]
                    ]
                ],
                'auto_detect' => true,
                'debug' => false
            ],
            'view' => [
                'paths' => [
                    '/Charcoal/Translator/Fixture/views'
                ]
            ]
        ]));
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
        $container['cache'] = (fn(): \Stash\Pool => new Pool(new Ephemeral()));
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     */
    public function registerTranslator(Container $container): void
    {
        $container['locales/config'] = (fn(Container $container): \Charcoal\Translator\LocalesConfig => new LocalesConfig($container['config']['locales']));

        $container['locales/manager'] = (fn(Container $container): \Charcoal\Translator\LocalesManager => new LocalesManager([
            'locales'            => $container['locales/config']['languages'],
            'default_language'   => $container['locales/config']['default_language'],
            'fallback_languages' => $container['locales/config']['fallback_languages']
        ]));

        $container['translator/config'] = (fn(Container $container): \Charcoal\Translator\TranslatorConfig => new TranslatorConfig($container['config']['translator']));

        $container['translator'] = function (Container $container): \Charcoal\Translator\Translator {
            $translator = new Translator([
                'manager'          => $container['locales/manager'],
                'message_selector' => new MessageSelector(),
                'cache_dir'        => null,
                'debug'            => $container['translator/config']['debug']
            ]);

            $translator->setFallbackLocales($container['locales/config']['fallback_languages']);

            return $translator;
        };
    }

    /**
     * Setup the framework's metadata loader interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerMetadataLoader(Container $container): void
    {
        $container['metadata/loader'] = (fn(Container $container): \Charcoal\Model\Service\MetadataLoader => new MetadataLoader([
            'cache'     => $container['cache'],
            'logger'    => $container['logger'],
            'base_path' => $container['config']['base_path'],
            'paths'     => [
                'metadata',
                // Standalone
                'vendor/charcoal/property/metadata',
                // Monorepo
                '/../property/metadata'
            ]
        ]));
    }

    /**
     * Setup the framework's data source factory.
     *
     * @param  Container $container A DI container.
     */
    public function registerSourceFactory(Container $container): void
    {
        $container['source/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'map' => [
                'database' => DatabaseSource::class
            ],
            'arguments'  => [[
                'logger' => $container['logger'],
                'cache'  => $container['cache'],
                'pdo'    => $container['database']
            ]]
        ]));
    }

    /**
     * Setup the framework's model factory.
     *
     * @param  Container $container A DI container.
     */
    public function registerModelFactory(Container $container): void
    {
        $container['model/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'arguments' => [[
                'container'         => $container,
                'logger'            => $container['logger'],
                'metadata_loader'   => $container['metadata/loader'],
                'source_factory'    => $container['source/factory'],
                'property_factory'  => $container['property/factory']
            ]]
        ]));
    }

    /**
     * Setup the framework's property factory.
     *
     * @param  Container $container A DI container.
     */
    public function registerPropertyFactory(Container $container): void
    {
        $container['property/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'resolver_options' => [
                'prefix' => '\\Charcoal\\Property\\',
                'suffix' => 'Property'
            ],
            'arguments' => [[
                'container'  => $container,
                'database'   => $container['database'],
                'logger'     => $container['logger'],
                'translator' => $container['translator']
            ]]
        ]));
    }

    /**
     * Setup the CLImate library.
     *
     * @param  Container $container A DI container.
     */
    public function registerClimate(Container $container): void
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

        $container['climate/util'] = (fn(Container $container): \League\CLImate\Util\UtilFactory => new UtilFactory($container['climate/system']));

        $container['climate'] = function (Container $container): \League\CLImate\CLImate {
            $climate = new CLImate();

            $climate->setOutput($container['climate/output']);
            $climate->setUtil($container['climate/util']);
            $climate->setReader($container['climate/reader']);

            return $climate;
        };
    }
}
