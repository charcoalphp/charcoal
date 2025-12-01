<?php

namespace Charcoal\Tests\Admin\Action;

use DI\Container;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
// From 'charcoal-admin'
use Charcoal\Admin\Action\LoginAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use Charcoal\Tests\Admin\Mock\UserProviderTrait;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginAction::class)]
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

        /** @var Container $container */
        $container = $this->container();

        $this->obj = new LoginAction([
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
    public function testRunWithoutParamsIs400()
    {
        $request  = $this->createMock(ServerRequest::class);
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

        $request = (new ServerRequest('GET', 'foo.bar'))->withQueryParams([
            'password' => 'asdfgh',
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
    protected function container(): Container
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
