<?php

namespace Charcoal\Tests\Admin\Action\Object;

use DI\Container;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\SaveAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SaveAction::class)]
class SaveActionTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * Tested Class.
     *
     * @var SaveAction
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
        $container = $this->container();

        $this->obj = new SaveAction([
            'logger'    => $container->get('logger'),
            'container' => $container
        ]);
    }

    /**
     * @return void
     */
    public function testAuthRequiredIsTrue()
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
    }

    /**
     * @return void
     */
    public function testRunWithoutObjTypeIs400()
    {
        $request  = $this->createMock(ServerRequest::class);
        $response = new Response();

        $response = $this->obj->run($request, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $results = $this->obj->results();
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
            $containerProvider->registerActionDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
