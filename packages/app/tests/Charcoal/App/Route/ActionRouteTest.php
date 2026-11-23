<?php

namespace Charcoal\Tests\App\Route;

// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\NullLogger;
// From Slim
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
// From 'charcoal-app'
use Charcoal\App\Action\AbstractAction;
use Charcoal\App\Exception\HttpException;
use Charcoal\App\Route\ActionRoute;
use Charcoal\Tests\App\ContainerProvider;
use Charcoal\Tests\AbstractTestCase;

class ActionRouteTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var ActionRoute
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
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new ActionRoute([
            'logger' => $container['logger'],
            'config' => []
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(ActionRoute::class, $this->obj);
    }

    public function testHttpExceptionFromInitIsConvertedToResponse()
    {
        $container = $this->container();

        $action = new class ([ 'logger' => new NullLogger() ]) extends AbstractAction {
            public function init(RequestInterface $request)
            {
                throw new HttpException(403, [ 'Location' => '/admin/login' ]);
            }

            public function results()
            {
                return [];
            }

            public function run(RequestInterface $request, ResponseInterface $response)
            {
                return $response;
            }
        };

        $container['action/factory'] = new class ($action) {
            private $action;

            public function __construct($action)
            {
                $this->action = $action;
            }

            public function create($controller)
            {
                unset($controller);

                return $this->action;
            }
        };

        $route = new ActionRoute([
            'config' => [
                'controller' => 'foo'
            ]
        ]);

        $request = Request::createFromEnvironment(Environment::mock());
        $response = $route($container, $request, new Response());

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('/admin/login', $response->getHeaderLine('Location'));
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
