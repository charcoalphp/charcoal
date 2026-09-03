<?php

namespace Charcoal\Tests\App\Middleware;

use Slim\Http\Response;

use Charcoal\App\Middleware\IpMiddleware;
use Charcoal\Tests\AbstractTestCase;

class IpMiddlewareTest extends AbstractTestCase
{
    /**
     * @var string|null
     */
    private $previousRemoteAddr;

    public function setUp(): void
    {
        $this->previousRemoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
    }

    public function tearDown(): void
    {
        if ($this->previousRemoteAddr === null) {
            unset($_SERVER['REMOTE_ADDR']);
        } else {
            $_SERVER['REMOTE_ADDR'] = $this->previousRemoteAddr;
        }
    }

    public function testDefaults()
    {
        $mw = new IpMiddleware([]);
        $this->assertEquals([
            'disallowed'           => [],
            'allowed'              => [],
            'error_message'        => '',
            'disallowed_redirect'  => '',
            'not_allowed_redirect' => '',
            'fail_on_invalid_ip'   => false,
        ], $mw->defaults());
    }

    public function testAllowsAnyIpWhenListsEmpty()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $called = false;

        $response = $this->invoke(new IpMiddleware([]), function ($req, $res) use (&$called) {
            $called = true;
            return $res->withStatus(200);
        });

        $this->assertTrue($called);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAllowsListedIp()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
        $called = false;

        $response = $this->invoke(new IpMiddleware([
            'allowed' => [ '203.0.113.10' ],
        ]), function ($req, $res) use (&$called) {
            $called = true;
            return $res;
        });

        $this->assertTrue($called);
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function testAllowsCidrRange()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.55';
        $called = false;

        $response = $this->invoke(new IpMiddleware([
            'allowed' => [ '203.0.113.0/24' ],
        ]), function ($req, $res) use (&$called) {
            $called = true;
            return $res;
        });

        $this->assertTrue($called);
    }

    public function testRejectsIpOutsideAllowlist()
    {
        $_SERVER['REMOTE_ADDR'] = '198.51.100.1';

        $response = $this->invoke(new IpMiddleware([
            'allowed'       => [ '203.0.113.10' ],
            'error_message' => 'blocked',
        ]), function ($req, $res) {
            return $res;
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('blocked', (string)$response->getBody());
    }

    public function testRedirectsWhenNotAllowed()
    {
        $_SERVER['REMOTE_ADDR'] = '198.51.100.1';

        $response = $this->invoke(new IpMiddleware([
            'allowed'              => [ '203.0.113.10' ],
            'not_allowed_redirect' => '/forbidden',
        ]), function ($req, $res) {
            return $res;
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/forbidden', $response->getHeaderLine('Location'));
    }

    public function testDisallowTakesPrecedence()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';

        $response = $this->invoke(new IpMiddleware([
            'allowed'       => [ '203.0.113.10' ],
            'disallowed'    => [ '203.0.113.10' ],
            'error_message' => 'denied',
        ]), function ($req, $res) {
            return $res;
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('denied', (string)$response->getBody());
    }

    public function testDisallowedRedirect()
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';

        $response = $this->invoke(new IpMiddleware([
            'disallowed'          => [ '203.0.113.0/24' ],
            'disallowed_redirect' => 'https://example.com/bye',
        ]), function ($req, $res) {
            return $res;
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('https://example.com/bye', $response->getHeaderLine('Location'));
    }

    public function testMissingIpFailsOpenByDefault()
    {
        unset($_SERVER['REMOTE_ADDR']);
        $called = false;

        $response = $this->invoke(new IpMiddleware([
            'allowed' => [ '203.0.113.10' ],
        ]), function ($req, $res) use (&$called) {
            $called = true;
            return $res->withStatus(204);
        });

        $this->assertTrue($called);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testMissingIpFailsClosedWhenConfigured()
    {
        unset($_SERVER['REMOTE_ADDR']);

        $response = $this->invoke(new IpMiddleware([
            'allowed'            => [ '203.0.113.10' ],
            'fail_on_invalid_ip' => true,
            'error_message'      => 'no-ip',
        ]), function ($req, $res) {
            return $res;
        });

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('no-ip', (string)$response->getBody());
    }

    public function testInvalidIpFailsOpenByDefault()
    {
        $_SERVER['REMOTE_ADDR'] = 'not-an-ip';
        $called = false;

        $this->invoke(new IpMiddleware([
            'allowed' => [ '203.0.113.10' ],
        ]), function ($req, $res) use (&$called) {
            $called = true;
            return $res;
        });

        $this->assertTrue($called);
    }

    /**
     * @param  IpMiddleware $middleware
     * @param  callable     $next
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function invoke(IpMiddleware $middleware, callable $next)
    {
        $request = $this->createMock(\Psr\Http\Message\RequestInterface::class);

        return $middleware($request, new Response(), $next);
    }
}
