<?php

namespace Charcoal\Tests\Cms\Route;

use InvalidArgumentException;

// From 'charcoal-cms'
use Charcoal\Cms\Event;
use Charcoal\Cms\Route\EventRoute;

/**
 * Test EventRoute
 */
class EventRouteTest extends AbstractRouteTestCase
{
    /**
     * Asserts that `EventRoute::__invoke()` method returns an HTTP Response object
     * with a 404 status code if the path does not resolve to any routable model.
     */
    public function testInvokeOnNonexistentModel(): void
    {
        $container = $this->getContainer();
        $router    = $this->createRouter([
            'path' => '/en/events/nonexistent',
        ]);

        $result = $router->pathResolvable($container);
        $this->assertFalse($result);

        $request   = $this->createHttpRequest();
        $response  = $this->createHttpResponse();

        $response = $router($container, $request, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Asserts that an Exception is raised from the `EventRoute::__invoke()` method
     * if the resolved model has a nonexistent template controller.
     *
     * The "template/factory" service throws an Exception when a model's template controller can not be found.
     */
    public function testInvokeOnExistingModelWithMissingTemplateController(): void
    {
        $this->insertMockRoutableContextObjects([
            'templateIdent' => 'nonexistent',
        ]);

        $container = $this->getContainer();
        $router    = $this->createRouter([
            'path' => '/en/events/launch-party',
        ]);

        $result = $router->pathResolvable($container);
        $this->assertTrue($result);

        $request   = $this->createHttpRequest();
        $response  = $this->createHttpResponse();

        $this->expectException(InvalidArgumentException::class);
        $router($container, $request, $response);
    }

    /**
     * Asserts that `EventRoute::__invoke()` method returns an HTTP Response object
     * with a 500 status code if the resolved model does not have a template identifier.
     */
    public function testInvokeOnExistingModelWithoutTemplateIdent(): void
    {
        $this->insertMockRoutableContextObjects([
            'templateIdent' => '',
        ]);

        $container = $this->getContainer();
        $router    = $this->createRouter([
            'path' => '/en/events/launch-party',
        ]);

        $result = $router->pathResolvable($container);
        $this->assertTrue($result);

        $request   = $this->createHttpRequest();
        $response  = $this->createHttpResponse();

        $response = $router($container, $request, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Asserts that `EventRoute::__invoke()` method returns an HTTP Response object
     * with a 500 status code if the resolved model does not have a rendered template view.
     */
    public function testInvokeOnExistingModelWithBadTemplateIdent(): void
    {
        $this->insertMockRoutableContextObjects([
            'templateIdent' => 'charcoal/tests/cms/mock/broken',
        ]);

        $container = $this->getContainer();
        $router    = $this->createRouter([
            'path' => '/en/events/launch-party',
        ]);

        $result = $router->pathResolvable($container);
        $this->assertTrue($result);

        $request   = $this->createHttpRequest();
        $response  = $this->createHttpResponse();

        $response = $router($container, $request, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    /**
     * Asserts that `EventRoute::__invoke()` method returns an HTTP Response object
     * with a 2XX status code if the path does resolve to a specific routable model.
     */
    public function testInvokeOnExistingModelWithTemplateIdent(): void
    {
        $this->insertMockRoutableContextObjects([
            'templateIdent' => 'charcoal/tests/cms/mock/event',
        ]);

        $container = $this->getContainer();
        $router    = $this->createRouter([
            'path' => '/en/events/launch-party',
        ]);

        $result = $router->pathResolvable($container);
        $this->assertTrue($result);

        $request   = $this->createHttpRequest();
        $response  = $this->createHttpResponse();

        $response = $router($container, $request, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<h1>Event</h1>', (string)$response->getBody());
    }

    /**
     * Create the dynamic route to test.
     *
     * @param  array $data The dynamic route dependencies.
     */
    protected function createRouter(array $data = []): \Charcoal\Cms\Route\EventRoute
    {
        return new EventRoute($data + [
            'config' => [],
            'path'   => '',
        ]);
    }

    /**
     * Create the model's database table to test lookup.
     *
     * @return void
     */
    protected function setUpRoutableContextModel()
    {
        $container = $this->getContainer();

        $event = $container['model/factory']->get(Event::class);
        if ($event->source()->tableExists() === false) {
            $event->source()->createTable();
        }
    }

    /**
     * Insert an entity into the model's database table to test a resolvable route.
     *
     * @param  array $data The model data.
     * @return void
     */
    protected function insertMockRoutableContextObjects(array $data = [])
    {
        $container = $this->getContainer();

        $event = $container['model/factory']->create(Event::class);

        $event->setData($data + [
            'id'    => 1,
            'title' => 'Launch Party',
            'slug'  => 'launch-party',
        ]);

        $event->save();
    }
}
