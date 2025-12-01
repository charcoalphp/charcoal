<?php

namespace Charcoal\Tests\Admin\Action\Object;

use DI\Container;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Collection;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\ReorderAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\SortableModel as Model;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReorderAction::class)]
class ReorderActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * The primary model to test with.
     *
     * @var string
     */
    private const MODEL = Model::class;

    /**
     * Store the tested instance.
     *
     * @var ReorderAction
     */
    private $action;

    /**
     * Store the object collection loader.
     *
     * @var CollectionLoader
     */
    private $collectionLoader;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $container = $this->container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->action = new ReorderAction([
            'logger'    => $container->get('logger'),
            'container' => $container
        ]);
    }

    /**
     * @return array
     */
    public function setUpObjects()
    {
        $container = $this->container();

        $model  = $container->get('model/factory')->create(self::MODEL);
        $source = $model->source();

        if (!$source->tableExists()) {
            $source->createTable();
        }

        $objs = [
            [ 'id' => 'foo', 'position' => 1 ],
            [ 'id' => 'bar', 'position' => 2 ],
            [ 'id' => 'baz', 'position' => 3 ],
            [ 'id' => 'qux', 'position' => 4 ],
        ];

        foreach ($objs as $obj) {
            $model->setData($obj)->save();
        }

        // Test initial order from data-source.
        $objs = $this->getObjects();
        $this->assertEquals([ 'foo', 'bar', 'baz', 'qux' ], $objs->keys());

        return $objs;
    }

    /**
     * @return Collection
     */
    public function getObjects()
    {
        if ($this->collectionLoader === null) {
            $container = $this->container();

            $loader = new CollectionLoader([
                'logger'     => $container->get('logger'),
                'factory'    => $container->get('model/factory'),
                'model'      => self::MODEL,
                'collection' => Collection::class
            ]);
            $loader->addOrder('position');

            $this->collectionLoader = $loader;
        }

        return $this->collectionLoader->load();
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsTrue()
    {
        $res = $this->callMethod($this->action, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     * @dataProvider runRequestProvider
     *
     * @param  integer $status  An HTTP status code.
     * @param  string  $success Whether the action was successful.
     * @param  array   $mock    The request parameters to test.
     * @return void
     */
    public function testRun($status, $success, array $mock)
    {
        if ($status === 200) {
            $this->setUpObjects();
        }

        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams($mock);
        $response = new Response();

        $response = $this->action->run($request, $response);
        $this->assertEquals($status, $response->getStatusCode());

        $results = $this->action->results();
        $this->assertEquals($success, $results['success']);

        if ($status === 200) {
            $keys = $this->getObjects()->keys();
            $this->assertEquals([ 'baz', 'bar', 'qux', 'foo' ], $keys);
        }
    }

    /**
     * @return array
     */
    public static function runRequestProvider()
    {
        return [
            [ 400, false, [] ],
            [ 400, false, [ 'obj_type' => self::MODEL ] ],
            [ 400, false, [ 'obj_type' => self::MODEL, 'order_property' => '5' ] ],
            [ 400, false, [ 'obj_type' => self::MODEL, 'order_property' => 'foobar' ] ],
            [ 500, false, [ 'obj_type' => self::MODEL, 'obj_orders' => ['xyzzy', 'qwerty'] ] ],
            [ 200, true,  [ 'obj_type' => self::MODEL, 'obj_orders' => ['baz', 'bar', 'qux', 'foo'] ] ],
        ];
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminServices($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
