<?php

namespace Charcoal\Tests\Admin\Action\Account;

use ReflectionClass;

// From Mockery
use Mockery as m;

// From Pimple
use Pimple\Container;

// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

// From 'charcoal-admin'
use Charcoal\Admin\Action\Account\LostPasswordAction;
use Charcoal\Admin\User;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class LostPasswordActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\Action\Account\LostPasswordAction $obj;

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

        $this->obj = new LostPasswordAction([
            'logger'    => $container['logger'],
            'container' => $container
        ]);
    }

    public function testAuthRequiredIsFalse(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertFalse($res);
    }

    public function testRunWithoutEmailReturns400(): void
    {
        $request  = Request::createFromEnvironment(Environment::mock());
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithoutRecaptchaReturns400(): void
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with(null)
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'email=foobar@foo.bar'
        ]));
        $response = new Response();

        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
    }

    public function testRunWithInvalidRecaptchaReturns400(): void
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with('foobar')
                    ->andReturn(false);

        $request = Request::createFromEnvironment(Environment::mock([
            'QUERY_STRING' => 'email=foobar@foo.bar&g-recaptcha-response=foobar'
        ]));
        $response = new Response();

        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
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
            $containerProvider->registerEmailFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
