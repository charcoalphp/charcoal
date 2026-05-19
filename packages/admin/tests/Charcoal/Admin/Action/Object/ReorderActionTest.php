<?php

namespace Charcoal\Tests\Admin\Action\Object;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\Collection;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\ReorderAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\SortableModel as Model;

/**
 *
 */
class ReorderActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * The primary model to test with.
     */
    private string $model = Model::class;

    /**
     * Store the tested instance.
     */
    private \Charcoal\Admin\Action\Object\ReorderAction $action;

    /**
     * Store the object collection loader.
     */
    private ?\Charcoal\Loader\CollectionLoader $collectionLoader = null;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerActionDependencies($container);

        $this->action = new ReorderAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return array
     */
    public function setUpObjects(): \ArrayAccess|array
    {
        $container = $this->container();

        $model  = $container['model/factory']->create($this->model);
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
    public function getObjects(): \ArrayAccess|array
    {
        if (!$this->collectionLoader instanceof \Charcoal\Loader\CollectionLoader) {
            $container = $this->container();

            $loader = new CollectionLoader([
                'logger'     => $container['logger'],
                'factory'    => $container['model/factory'],
                'model'      => $this->model,
                'collection' => Collection::class
            ]);
            $loader->addOrder('position');

            $this->collectionLoader = $loader;
        }

        return $this->collectionLoader->load();
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->action, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     *
     * @param  integer $status  An HTTP status code.
     * @param  string  $success Whether the action was successful.
     * @param  array   $mock    The request parameters to test.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('runRequestProvider')]
    public function testRun(int $status, bool $success, array $mock): void
    {
        if ($status === 200) {
            $this->setUpObjects();
        }

        $request  = Request::createFromEnvironment(Environment::mock($mock));
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

    public static function runRequestProvider(): array
    {
        $model = Model::class;
        return [
            [ 400, false, [] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$model ] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$model.'&order_property=5' ] ],
            [ 400, false, [ 'QUERY_STRING' => 'obj_type='.$model.'&order_property=foobar' ] ],
            [ 500, false, [ 'QUERY_STRING' => 'obj_type='.$model.'&obj_orders[]=xyzzy&obj_orders[]=qwerty' ] ],
            [ 200, true,  [ 'QUERY_STRING' => 'obj_type='.$model.'&obj_orders[]=baz&obj_orders[]=bar&obj_orders[]=qux&obj_orders[]=foo' ] ],
        ];
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerAdminServices($container);

            $this->container = $container;
        }
        return $this->container;
    }
}
