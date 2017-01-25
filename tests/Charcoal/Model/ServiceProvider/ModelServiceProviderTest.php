<?php

namespace Charcoal\Tests\Model\ServiceProvider;

use \PDO;

use \Psr\Log\NullLogger;
use \Cache\Adapter\Void\VoidCachePool;

use \Pimple\Container;

use \Charcoal\Factory\FactoryInterface;

use \Charcoal\App\AppConfig;

use \Charcoal\View\GenericView;
use \Charcoal\View\Php\PhpEngine;
use \Charcoal\View\Php\PhpLoader;

use \Charcoal\Model\ServiceProvider\ModelServiceProvider;
use \Charcoal\Model\Service\ModelBuilder;
use \Charcoal\Model\Service\ModelLoaderBuilder;
use \Charcoal\Model\Service\MetadataLoader;

/**
 *
 */
class ModelServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModelServiceProvider
     */
    public $obj;


    public function setUp()
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
     * @return Container
     */
    private function container()
    {
        $container = new Container();

        $container['cache']  = new VoidCachePool();
        $container['config'] = new AppConfig([
            'metadata'  => [
                'paths' => []
            ]
        ]);
        $container['database'] = new PDO('sqlite::memory:');
        $container['logger']   = new NullLogger();

        $container['view/loader'] = new PhpLoader([
            'logger'    => $container['logger'],
            'base_path' => dirname(__DIR__),
            'paths'     => [ 'views' ]
        ]);
        $container['view/engine'] = new PhpEngine([
            'logger' => $container['logger'],
            'loader' => $container['view/loader']
        ]);

        $container['view'] = new GenericView([
            'logger' => $container['logger'],
            'engine' => $container['view/engine']
        ]);
        return $container;
    }

    public function testRegisterSetsModelFactory()
    {
        $container = $this->container();
        $this->obj->register($container);

        //$this->assertTrue(isset($this->container['model/factory']));
        $this->assertInstanceOf(FactoryInterface::class, $container['model/factory']);

        $this->assertTrue(isset($container['property/factory']));
        $this->assertTrue(isset($container['source/factory']));
    }

    public function testRegisterSetsModelBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['model/builder']));
        $this->assertInstanceOf(ModelBuilder::class, $container['model/builder']);
    }

    public function testRegisterSetsModelLoaderBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['model/loader/builder']));
        $this->assertInstanceOf(ModelLoaderBuilder::class, $container['model/loader/builder']);
    }

    public function testRegisterSetsMetadataLoader()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue(isset($container['metadata/loader']));
        $this->assertInstanceOf(MetadataLoader::class, $container['metadata/loader']);
    }

    public function testRegisterSetsPropertyFactory()
    {
    }
}
