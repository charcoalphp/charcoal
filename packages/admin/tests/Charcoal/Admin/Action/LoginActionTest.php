<?php

namespace Charcoal\Tests\Admin\Action;

use PDO;
use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\LoginAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class LoginActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Action\LoginAction $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new LoginAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertFalse($res);
    }

    public function testRunWithoutParamsIs400(): void
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testRunWithInvalidCredentials(): void
    {
        $this->createUser('foo@bar.com');

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'password=asdfgh'
        ]));
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    /*
    public function testRunWithValidCredentials()
    {
        $this->createUser('foo@bar.com');
    
        $request = Request::createFromEnvironment(Environment::mock([
           'QUERY_STRING' => 'password=qwerty'
        ]));
        $response = new Response();
    
        $response = $this->obj->run($request, $response);
        $this->assertEquals(200, $response->getStatusCode());
    
        $results = $this->obj->results();
        $this->assertTrue($results['success']);
    }
    */
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
