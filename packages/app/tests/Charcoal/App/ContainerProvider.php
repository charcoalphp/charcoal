<?php

namespace Charcoal\Tests\App;

use PDO;

// From Mockery
use Mockery;

// From PSR-3
use Psr\Log\NullLogger;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;

// From Slim
use Slim\Http\Uri;

// From Pimple
use Pimple\Container;

// From 'league/climate'
use League\CLImate\CLImate;
use League\CLImate\Util\System\Linux;
use League\CLImate\Util\Output;
use League\CLImate\Util\Reader\Stdin;
use League\CLImate\Util\UtilFactory;

// From 'charcoal-factory'
use Charcoal\Factory\GenericFactory as Factory;

// From 'charcoal-cache'
use Charcoal\Cache\CacheConfig;

// From 'charcoal-app'
use Charcoal\App\AppConfig;
use Charcoal\App\Module\ModuleInterface;
use Charcoal\App\Template\WidgetBuilder;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\DatabaseSource;

// From 'charcoal-view'
use Charcoal\View\GenericView;
use Charcoal\View\Mustache\MustacheEngine;
use Charcoal\View\Mustache\MustacheLoader;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
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
        $this->registerDatabase($container);
        $this->registerLogger($container);
        $this->registerCache($container);
    }

    /**
     * Setup the application's base URI.
     *
     * @param  Container $container A DI container.
     */
    public function registerBaseUrl(Container $container): void
    {
        $container['base-url'] = (fn(Container $container) => Uri::createFromString('https://example.com:8080/foo/bar?abc=123'));
    }

    /**
     * Setup the application configset.
     *
     * @param  Container $container A DI container.
     */
    public function registerConfig(Container $container): void
    {
        $container['config'] = (fn(Container $container): \Charcoal\App\AppConfig => new AppConfig([
            'base_path' => realpath(__DIR__ . '/../../..'),
        ]));
    }

    public function registerWidgetFactory(Container $container): void
    {
        $this->registerLogger($container);

        $container['widget/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'resolver_options' => [
                'suffix' => 'Widget'
            ],
            'arguments' => [[
                'container' => $container,
                'logger'    => $container['logger']
            ]]
        ]));
    }

    public function registerWidgetBuilder(Container $container): void
    {
        $this->registerWidgetFactory($container);

        $container['widget/builder'] = (fn(Container $container): \Charcoal\App\Template\WidgetBuilder => new WidgetBuilder($container['widget/factory'], $container));
    }

    public function registerClimate(Container $container): void
    {
        $container['climate/system'] = function (Container $container) {
            $system = Mockery::mock(Linux::class);
            $system->shouldReceive('hasAnsiSupport')->andReturn(true);
            $system->shouldReceive('width')->andReturn(80);

            return $system;
        };

        $container['climate/output'] = function (Container $container) {
            $output = Mockery::mock(Output::class);
            $output->shouldReceive('persist')->andReturn($output);
            $output->shouldReceive('sameLine')->andReturn($output);
            $output->shouldReceive('write');

            return $output;
        };

        $container['climate/reader'] = function (Container $container) {
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

    /**
     * Setup the framework's view renderer.
     *
     * @param  Container $container A DI container.
     */
    public function registerView(Container $container): void
    {
        $container['view/loader'] = (fn(Container $container): \Charcoal\View\Mustache\MustacheLoader => new MustacheLoader([
            'logger'    => $container['logger'],
            'base_path' => $container['config']['base_path'],
            'paths'     => [
                'views'
            ]
        ]));

        $container['view/engine'] = (fn(Container $container): \Charcoal\View\Mustache\MustacheEngine => new MustacheEngine([
            'logger' => $container['logger'],
            'cache'  => MustacheEngine::DEFAULT_CACHE_PATH,
            'loader' => $container['view/loader']
        ]));

        $container['view'] = (fn(Container $container): \Charcoal\View\GenericView => new GenericView([
            'logger' => $container['logger'],
            'engine' => $container['view/engine']
        ]));
    }

    /**
     * Setup the application's translator service.
     *
     * @param  Container $container A DI container.
     */
    public function registerTranslator(Container $container): void
    {
        $container['locales/manager'] = (fn(Container $container): \Charcoal\Translator\LocalesManager => new LocalesManager([
            'locales' => [
                'en' => [ 'locale' => 'en-US' ]
            ]
        ]));

        $container['translator'] = (fn(Container $container): \Charcoal\Translator\Translator => new Translator([
            'manager' => $container['locales/manager']
        ]));
    }

    /**
     * Setup the application's logging interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerLogger(Container $container): void
    {
        $container['logger'] = (fn(Container $container): \Psr\Log\NullLogger => new NullLogger());
    }

    /**
     * Setup the application's caching interface.
     *
     * @param  Container $container A DI container.
     */
    public function registerCache(Container $container): void
    {
        $container['cache/config'] = (fn(Container $container): \Charcoal\Cache\CacheConfig => new CacheConfig());

        $container['cache'] = (fn($container): \Stash\Pool => new Pool());
    }

    public function registerDatabase(Container $container): void
    {
        $container['database'] = function (Container $container): \PDO {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }

    public function registerMetadataLoader(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerCache($container);

        $container['metadata/loader'] = (fn(Container $container): \Charcoal\Model\Service\MetadataLoader => new MetadataLoader([
            'logger'    => $container['logger'],
            'cache'     => $container['cache'],
            'base_path' => $container['config']['base_path'],
            'paths'     => [
                'metadata',
                // Standalone
                'vendor/charcoal/object/metadata',
                'vendor/charcoal/user/metadata',
                // Monorepo
                '/../object/metadata',
                '/../user/metadata'
            ]
        ]));
    }

    public function registerSourceFactory(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerDatabase($container);

        $container['source/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'map' => [
                'database' => DatabaseSource::class
            ],
            'arguments'  => [[
                'logger' => $container['logger'],
                'pdo'    => $container['database']
            ]]
        ]));
    }

    public function registerPropertyFactory(Container $container): void
    {
        $this->registerTranslator($container);
        $this->registerDatabase($container);
        $this->registerLogger($container);

        $container['property/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'resolver_options' => [
                'prefix' => '\\Charcoal\\Property\\',
                'suffix' => 'Property'
            ],
            'arguments' => [[
                'container'  => $container,
                'database'   => $container['database'],
                'translator' => $container['translator'],
                'logger'     => $container['logger']
            ]]
        ]));
    }

    public function registerModelFactory(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerMetadataLoader($container);
        $this->registerPropertyFactory($container);
        $this->registerSourceFactory($container);

        $container['model/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'arguments' => [[
                'container'        => $container,
                'logger'           => $container['logger'],
                'metadata_loader'  => $container['metadata/loader'],
                'property_factory' => $container['property/factory'],
                'source_factory'   => $container['source/factory']
            ]]
        ]));
    }

    public function registerCollectionLoader(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerModelFactory($container);

        $container['model/collection/loader'] = (fn(Container $container): \Charcoal\Loader\CollectionLoader => new \Charcoal\Loader\CollectionLoader([
            'logger'  => $container['logger'],
            'factory' => $container['model/factory']
        ]));
    }

    public function registerModuleFactory(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerDatabase($container);

        $container['module/factory'] = (fn(Container $container): \Charcoal\Factory\GenericFactory => new Factory([
            'base_class'       => ModuleInterface::class,
            'resolver_options' => [
                'suffix' => 'Module'
            ],
            'arguments'  => [[
                'logger' => $container['logger']
            ]]
        ]));
    }

    public function registerAppDependencies(Container $container): void
    {
        $this->registerConfig($container);
        $this->registerBaseUrl($container);
        $this->registerLogger($container);
        $this->registerCache($container);
        $this->registerTranslator($container);
        $this->registerModuleFactory($container);
    }

    public function registerActionDependencies(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerBaseUrl($container);
    }

    public function registerTemplateDependencies(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerBaseUrl($container);
    }

    public function registerWidgetDependencies(Container $container): void
    {
        $this->registerLogger($container);
        $this->registerTranslator($container);
        $this->registerView($container);
        $this->registerBaseUrl($container);
    }
}
