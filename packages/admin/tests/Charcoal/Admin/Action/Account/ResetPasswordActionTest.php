<?php

namespace Charcoal\Tests\Admin\Action\Account;

// From Mockery
use Mockery as m;
use DI\Container;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Account\ResetPasswordAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResetPasswordAction::class)]
class ResetPasswordActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     *
     * @var ResetPasswordAction
     */
    public $obj;

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

        $this->obj = new ResetPasswordAction([
            'logger'    => $container->get('logger'),
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
    public function testRunWithoutTokenReturns400()
    {
        $request  = $this->createMock(ServerRequest::class);
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutEmailReturns400()
    {
        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'token' => 'foobar'
        ]);
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutPasswordReturns400()
    {
        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'token' => 'foobar',
            'email' => 'foobar@foo.bar'
        ]);
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutMatchingPasswordsReturns400()
    {
        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'token' => 'foobar',
            'email' => 'foobar@foo.bar',
            'password1' => 'foo',
            'password2' => 'bar',
        ]);
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithoutRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with(null)
                    ->andReturn(false);

        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'token' => 'foobar',
            'email' => 'foobar@foo.bar',
            'password1' => 'foo',
            'password2' => 'foo',
        ]);
        $response = new Response();

        /** @var ResetPasswordAction $mock */
        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
    }

    /**
     * @return void
     */
    public function testRunWithInvalidRecaptchaReturns400()
    {
        $mock = m::mock($this->obj);
        $mock->shouldAllowMockingProtectedMethods()
             ->shouldReceive('validateCaptcha')
                ->with('foobar')
                    ->andReturn(false);

        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'token' => 'foobar',
            'email' => 'foobar@foo.bar',
            'password1' => 'foo',
            'password2' => 'foo',
            'g-recaptcha-response' => 'foobar'
        ]);
        $response = new Response();

        /** @var ResetPasswordAction $mock */
        $response = $mock->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $mock->results();
        $this->assertFalse($results['success']);
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
