<?php

namespace Charcoal\Tests\Admin\Action;

use ReflectionClass;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\LogoutAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;

/**
 *
 */
class LogoutActionTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use UserProviderTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Action\LogoutAction $obj;

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
        $this->obj = new LogoutAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    public function testRunWithUnauthenticatedUser(): void
    {
        $this->createUser('foo@bar.com');

        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithAuthenticatedUser(): void
    {
        $user = $this->createUser('foo@bar.com');
        $this->getAuthenticator()->setUser($user);

        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(204, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertTrue($results['success']);
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
