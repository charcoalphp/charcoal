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
     *
     * @var LoginAction
     */
    private $obj;

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
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new LoginAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsFalse()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertFalse($res);
    }

    /**
     * @return void
     */
    public function testRunWithoutParamsIs400()
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testRunWithInvalidCredentials()
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
     * Open-redirect payloads must not become next_url after a failed/partial login path.
     * Successful login with next_url is covered via setSuccessUrl allowlisting on AdminAction.
     *
     * @return void
     */
    public function testNextUrlParamIsSanitizedOnSuccessUrl()
    {
        $this->obj->setSuccessUrl('https://evil.example/phish');
        $results = $this->obj->results();
        $this->assertNotEquals('https://evil.example/phish', $results['next_url']);
        $this->assertEquals('/admin/', $this->obj->successUrl());

        $this->obj->setSuccess(true);
        $this->obj->setSuccessUrl('/admin/object/edit');
        $results = $this->obj->results();
        $this->assertEquals('/admin/object/edit', $results['next_url']);
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
     *
     * @return Container
     */
    protected function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerActionDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
