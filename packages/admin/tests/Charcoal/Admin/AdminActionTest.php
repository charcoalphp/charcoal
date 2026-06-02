<?php

namespace Charcoal\Tests\Admin;

use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

// From PSR-7
use Psr\Http\Message\RequestInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AdminActionTest extends AbstractTestCase
{
    use AssertionsTrait;
    use ReflectionsTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Admin\AdminAction $obj;

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

        $this->obj = new class([
            'logger'    => $container['logger'],
            'container' => $container
        ]) extends AdminAction {
            public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
            {
                return $response;
            }
        };
    }

    /**
     * Asserts that success behaves as expected.
     * (Actually test base admin action).
     * - false by default
     * - setSuccess is chainable
     * - setSuccess can be called with non-boolean (0 or 1, for example) values
     * - success can be set by ArrayAccess
     * - success can be set with get()
     * - success can be accessed by ArrayAccess
     */
    public function testSuccess(): void
    {
        $this->assertFalse($this->obj->success());
        $ret = $this->obj->setSuccess(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->success());

        $this->obj->setSuccess(0);
        $this->assertFalse($this->obj->success());

        $this->obj['success'] = true;
        $this->assertTrue($this->obj->success());

        $this->obj->set('success', false);
        $this->assertFalse($this->obj['success']);
    }

    public function testFeedback(): void
    {
        $this->assertFalse($this->obj->hasFeedbacks());
        $this->assertEquals([], $this->obj->feedbacks());
        $this->assertEquals(0, $this->obj->numFeedbacks());

        $entryId = $this->obj->addFeedback('error', 'Message');
        $entries = $this->obj->feedbacks();
        $entry   = reset($entries);

        $this->assertArraySubset([ 'id'      => $entryId  ], $entry);
        $this->assertArraySubset([ 'type'    => 'danger'  ], $entry);
        $this->assertArraySubset([ 'level'   => 'error'   ], $entry);
        $this->assertArraySubset([ 'message' => 'Message' ], $entry);

        $this->assertTrue($this->obj->hasFeedbacks());
        $this->assertEquals(1, $this->obj->numFeedbacks());
    }

    public function testAdminUrl(): void
    {
        $this->assertEquals('/admin/', $this->obj->adminUrl());
    }

    public function testAuthRequiredIsTrue(): void
    {
        $res = $this->callMethod($this->obj, 'authRequired');
        $this->assertTrue($res);
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
