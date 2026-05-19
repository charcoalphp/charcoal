<?php

namespace Charcoal\Tests\Admin\Action\Object;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\LoadAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class LoadActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Action\Object\LoadAction $obj;

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

        $this->obj = new LoadAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    public function testRunWithoutObjTypeIs400(): void
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRun(): void
    {
        $user = $this->createUser('foo@bar.com');

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertTrue($results['success']);

        $this->assertEquals(json_encode([ $user ]), json_encode($results['collection']));
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerActionDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
