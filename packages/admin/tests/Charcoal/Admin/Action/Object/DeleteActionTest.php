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
use Charcoal\Admin\Action\Object\DeleteAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class DeleteActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Action\Object\DeleteAction $obj;

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

        $this->obj = new DeleteAction([
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

    public function testRunWithoutObjIdIs400(): void
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithInvalidObject(): void
    {
        $email = 'foobar@foo.bar';
        $this->createUser($email);
        $this->assertTrue($this->userExists($email));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id=bazqux'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);

        $this->assertTrue($this->userExists($email));
    }

    public function testRunWithObjectDelete(): void
    {
        $email = 'foobar@foo.bar';
        $user = $this->createUser($email);
        $this->assertTrue($this->userExists($email));

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'obj_type=charcoal/admin/user&obj_id='.$user->id()
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertTrue($results['success']);

        $this->assertFalse($this->userExists($email));
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
