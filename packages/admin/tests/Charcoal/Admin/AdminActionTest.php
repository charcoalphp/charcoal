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
     * @param  string  $url      Candidate URL.
     * @param  boolean $expected Whether it should be accepted.
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSafeRedirectUrls')]
    public function testIsSafeRedirectUrl($url, $expected)
    {
        $this->assertSame($expected, $this->obj->isSafeRedirectUrl($url));
    }

    /**
     * @return array
     */
    public static function provideSafeRedirectUrls()
    {
        return [
            'path absolute'           => [ '/admin/object/edit', true ],
            'path relative'           => [ 'object/edit?obj_type=foo', true ],
            'query only'              => [ '?notice=ok', true ],
            'empty'                   => [ '', false ],
            'protocol relative'       => [ '//evil.example/', false ],
            'external https'          => [ 'https://evil.example/', false ],
            'javascript scheme'       => [ 'javascript:alert(1)', false ],
            'data scheme'             => [ 'data:text/html,hi', false ],
            'backslash authority'     => [ '/\\evil.example/', false ],
            'newline injection'       => [ "/admin/\nLocation: https://evil.example/", false ],
        ];
    }

    /**
     * @return void
     */
    public function testIsSafeRedirectUrlSameOrigin()
    {
        $this->callMethod($this->obj, 'setBaseUrl', [
            \Slim\Http\Uri::createFromString('https://example.com/')
        ]);
        $this->callMethod($this->obj, 'setAdminUrl', [
            \Slim\Http\Uri::createFromString('https://example.com/admin/')
        ]);

        $this->assertTrue($this->obj->isSafeRedirectUrl('https://example.com/admin/'));
        $this->assertTrue($this->obj->isSafeRedirectUrl('https://example.com/foo'));
        $this->assertFalse($this->obj->isSafeRedirectUrl('https://evil.example/'));
        $this->assertFalse($this->obj->isSafeRedirectUrl('http://example.com/admin/'));
        $this->assertFalse($this->obj->isSafeRedirectUrl('//evil.example/path'));
    }

    /**
     * @return void
     */
    public function testSetSuccessUrlRejectsOpenRedirect()
    {
        $this->obj->setSuccessUrl('https://evil.example/phish');
        $this->assertEquals('/admin/', $this->obj->successUrl());

        $this->obj->setSuccessUrl('/admin/object/edit?obj_type=foo');
        $this->assertEquals('/admin/object/edit?obj_type=foo', $this->obj->successUrl());
    }

    /**
     * @return void
     */
    public function testSetNextUrlMapsToSuccessUrl()
    {
        $this->obj->setSuccess(true);
        $this->obj->setNextUrl('object/edit');
        $this->assertEquals('object/edit', $this->obj->successUrl());
        $this->assertEquals('object/edit', $this->obj->redirectUrl());
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
