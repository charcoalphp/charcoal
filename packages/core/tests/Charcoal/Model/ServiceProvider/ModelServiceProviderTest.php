<?php

namespace Charcoal\Tests\Model\ServiceProvider;

use PDO;

// From PSR-3
use Psr\Log\NullLogger;

// From 'cache/void-adapter' (PSR-6)
use Cache\Adapter\Void\VoidCachePool;

// From 'tedivm/stash' (PSR-6)
use Stash\Pool;
use Stash\Driver\Ephemeral;

// From Pimple
use Pimple\Container;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-app'
use Charcoal\App\AppConfig;

// From 'charcoal-view'
use Charcoal\View\GenericView;
use Charcoal\View\Php\PhpEngine;
use Charcoal\View\Php\PhpLoader;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;

// From 'charcoal-core'
use Charcoal\Model\ServiceProvider\ModelServiceProvider;
use Charcoal\Model\Service\ModelBuilder;
use Charcoal\Model\Service\ModelLoaderBuilder;
use Charcoal\Model\Service\MetadataConfig;
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ModelServiceProviderTest extends AbstractTestCase
{
    /**
     * @var ModelServiceProvider
     */
    public $obj;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = new ModelServiceProvider();
    }

    /**
     * Get a container with its requirements registered.
     *
     * The requirements are:
     * - cache
     * - config
     * - database
     * - logger
     * - view
     *
     * @todo   Use ContainerIntegrationTrait?
     * @return Container
     */
    private function container()
    {
        $container = new Container();

        $container['logger']   = new NullLogger();
        $container['cache']    = new Pool(new Ephemeral());
        $container['database'] = new PDO('sqlite::memory:');

        $container['config'] = new AppConfig([
            'base_path' => sys_get_temp_dir(),
            'metadata'  => [
                'paths' => [],
            ],
        ]);

        $container['view/loader'] = new PhpLoader([
            'logger'    => $container['logger'],
            'base_path' => dirname(__DIR__),
            'paths'     => [ 'views' ],
        ]);

        $container['view/engine'] = new PhpEngine([
            'logger' => $container['logger'],
            'loader' => $container['view/loader'],
        ]);

        $container['view'] = new GenericView([
            'logger' => $container['logger'],
            'engine' => $container['view/engine'],
        ]);

        $container['locales/manager'] = new LocalesManager([
            'locales' => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
        ]);
        $container['translator'] = new Translator([
            'manager' => $container['locales/manager'],
        ]);

        return $container;
    }

    /**
     * @return void
     */
    public function testFactories()
    {
        $container = $this->container();

        $this->obj->register($container);

        $this->assertTrue(isset($container['model/factory']));
        $this->assertInstanceOf(FactoryInterface::class, $container['model/factory']);

        $this->assertTrue(isset($container['property/factory']));
        $this->assertInstanceOf(FactoryInterface::class, $container['property/factory']);

        $this->assertTrue(isset($container['source/factory']));
        $this->assertInstanceOf(FactoryInterface::class, $container['source/factory']);
    }

    /**
     * @return void
     */
    public function testRegisterSetsModelBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['model/builder']));
        $this->assertInstanceOf(ModelBuilder::class, $container['model/builder']);
    }

    /**
     * @return void
     */
    public function testRegisterSetsModelLoaderBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['model/loader/builder']));
        $this->assertInstanceOf(ModelLoaderBuilder::class, $container['model/loader/builder']);
    }

    /**
     * @return void
     */
    public function testRegisterSetsMetadataLoader()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['metadata/loader']));
        $this->assertInstanceOf(MetadataLoader::class, $container['metadata/loader']);
    }

    /**
     * @return void
     */
    public function testExtraMetadataPaths()
    {
        $container = new Container([
            'config' => [
                'base_path' => dirname(dirname(dirname(dirname(__DIR__)))),
            ],
            'module/classes' => [
                'Charcoal\\Tests\\Mock\\MockModule',
            ],
        ]);

        $provider = new ModelServiceProvider();
        $provider->register($container);

        $metadataConfig = $container['metadata/config'];
        $this->assertContains('tests/Charcoal/Model/metadata', $metadataConfig->paths());
    }
}
