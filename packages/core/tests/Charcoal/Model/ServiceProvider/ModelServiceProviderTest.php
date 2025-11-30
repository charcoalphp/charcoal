<?php

namespace Charcoal\Tests\Model\ServiceProvider;

use PDO;
// From PSR-3
use Psr\Log\NullLogger;
// From 'tedivm/stash' (PSR-6)
use Stash\Pool;
use Stash\Driver\Ephemeral;
use DI\Container;
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
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelServiceProvider::class)]
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

        $container->set('logger', new NullLogger());
        $container->set('cache', new Pool(new Ephemeral()));
        $container->set('database', new PDO('sqlite::memory:'));

        $container->set('config', new AppConfig([
            'base_path' => sys_get_temp_dir(),
            'metadata'  => [
                'paths' => [],
            ],
        ]));

        $container->set('view/loader', new PhpLoader([
            'logger'    => $container->get('logger'),
            'base_path' => dirname(__DIR__),
            'paths'     => [ 'views' ],
        ]));

        $container->set('view/engine', new PhpEngine([
            'logger' => $container->get('logger'),
            'loader' => $container->get('view/loader'),
        ]));

        $container->set('view', new GenericView([
            'logger' => $container->get('logger'),
            'engine' => $container->get('view/engine'),
        ]));

        $container->set('locales/manager', new LocalesManager([
            'locales' => [
                'en' => [
                    'locale' => 'en-US',
                ],
            ],
        ]));
        $container->set('translator', new Translator([
            'manager' => $container->get('locales/manager'),
        ]));

        return $container;
    }

    /**
     * @return void
     */
    public function testFactories()
    {
        $container = $this->container();

        $this->obj->register($container);

        $this->assertTrue($container->has('model/factory'));
        $this->assertInstanceOf(FactoryInterface::class, $container->get('model/factory'));

        $this->assertTrue($container->has('property/factory'));
        $this->assertInstanceOf(FactoryInterface::class, $container->get('property/factory'));

        $this->assertTrue($container->has('source/factory'));
        $this->assertInstanceOf(FactoryInterface::class, $container->get('source/factory'));
    }

    /**
     * @return void
     */
    public function testRegisterSetsModelBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue($container->has('model/builder'));
        $this->assertInstanceOf(ModelBuilder::class, $container->get('model/builder'));
    }

    /**
     * @return void
     */
    public function testRegisterSetsModelLoaderBuilder()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue($container->has('model/loader/builder'));
        $this->assertInstanceOf(ModelLoaderBuilder::class, $container->get('model/loader/builder'));
    }

    /**
     * @return void
     */
    public function testRegisterSetsMetadataLoader()
    {
        $container = $this->container();
        $this->obj->register($container);

        $this->assertTrue($container->has('metadata/loader'));
        $this->assertInstanceOf(MetadataLoader::class, $container->get('metadata/loader'));
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

        $metadataConfig = $container->get('metadata/config');
        $this->assertContains('tests/Charcoal/Model/metadata', $metadataConfig->paths());
    }
}
