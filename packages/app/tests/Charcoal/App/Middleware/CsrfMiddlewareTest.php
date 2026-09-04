<?php

namespace Charcoal\Tests\App\Middleware;

use InvalidArgumentException;
use Mockery;
use Slim\Csrf\Guard;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

use Charcoal\App\Middleware\CsrfMiddleware;
use Charcoal\Tests\AbstractTestCase;

class CsrfMiddlewareTest extends AbstractTestCase
{
    public function setUp(): void
    {
        $_SESSION['csrf'] = [];
    }

    public function tearDown(): void
    {
        Mockery::close();
        unset($_SESSION['csrf']);
    }

    public function testDefaults()
    {
        $mw = new CsrfMiddleware([ 'guard' => new Guard() ]);
        $this->assertEquals([
            'included_path'   => [],
            'excluded_path'   => [],
            'failure_message' => 'Invalid or expired form token. Please refresh the page and try again.',
            'failure_body'    => [
                'success' => false,
                'message' => '{{message}}',
            ],
        ], $mw->defaults());
    }

    public function testConstructorRequiresGuard()
    {
        $this->expectException(InvalidArgumentException::class);
        new CsrfMiddleware([]);
    }

    public function testConstructorRejectsWrongGuardType()
    {
        $this->expectException(InvalidArgumentException::class);
        new CsrfMiddleware([ 'guard' => new \stdClass() ]);
    }

    public function testInvalidIncludedPathPatternThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        new CsrfMiddleware([
            'guard'         => new Guard(),
            'included_path' => [ '(unclosed' ],
        ]);
    }

    public function testInvalidExcludedPathPatternThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        new CsrfMiddleware([
            'guard'         => new Guard(),
            'excluded_path' => [ '(unclosed' ],
        ]);
    }

    public function testIncludedPathEmptyMeansEverywhere()
    {
        $mw = new CsrfMiddleware([ 'guard' => new Guard() ]);
        $called = false;

        $response = $this->invoke(
            $mw,
            $this->request('POST', '/anything/at/all'),
            function ($req, $res) use (&$called) {
                $called = true;
                return $res;
            }
        );

        $this->assertFalse($called);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testUnmatchedPathPassesThrough()
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('setFailureCallable')->once();
        $guard->shouldNotReceive('__invoke');

        $mw = new CsrfMiddleware([
            'guard'         => $guard,
            'included_path' => [ '^/api/v1/form/' ],
        ]);

        $called = false;
        $response = $this->invoke(
            $mw,
            $this->request('GET', '/destinations/kuujjuaq'),
            function ($req, $res) use (&$called) {
                $called = true;
                return $res->withStatus(200);
            }
        );

        $this->assertTrue($called);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testExcludedPathOverridesIncludedPath()
    {
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('setFailureCallable')->once();
        $guard->shouldNotReceive('__invoke');

        $mw = new CsrfMiddleware([
            'guard'         => $guard,
            'included_path' => [ '^/api/v1/form/' ],
            'excluded_path' => [ '^/api/v1/form/exempt' ],
        ]);

        $called = false;
        $this->invoke(
            $mw,
            $this->request('POST', '/api/v1/form/exempt-example'),
            function ($req, $res) use (&$called) {
                $called = true;
                return $res;
            }
        );

        $this->assertTrue($called);
    }

    public function testMissingTokenIsRejected()
    {
        $mw = new CsrfMiddleware([
            'guard'         => new Guard(),
            'included_path' => [ '^/api/v1/form/' ],
        ]);

        $called = false;
        $response = $this->invoke(
            $mw,
            $this->request('POST', '/api/v1/form/cargo-account-registration', [ 'email' => 'a@b.com' ]),
            function ($req, $res) use (&$called) {
                $called = true;
                return $res;
            }
        );

        $this->assertFalse($called);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertFalse($body['success']);
        $this->assertNotEmpty($body['message']);
    }

    public function testValidTokenPasses()
    {
        $guard = new Guard();
        $mw = new CsrfMiddleware([
            'guard'         => $guard,
            'included_path' => [ '^/api/v1/form/' ],
        ]);

        // What AbstractFormTemplate::csrfFields() does when rendering the form.
        $guard->validateStorage();
        $token = $guard->generateToken();

        $called = false;
        $response = $this->invoke(
            $mw,
            $this->request('POST', '/api/v1/form/cargo-account-registration', array_merge(
                [ 'email' => 'a@b.com' ],
                $token
            )),
            function ($req, $res) use (&$called) {
                $called = true;
                return $res;
            }
        );

        $this->assertTrue($called);
        $this->assertNotEquals(400, $response->getStatusCode());
    }

    public function testCustomFailureMessage()
    {
        $mw = new CsrfMiddleware([
            'guard'           => new Guard(),
            'included_path'   => [ '^/api/v1/form/' ],
            'failure_message' => 'Please try submitting the form again.',
        ]);

        $response = $this->invoke(
            $mw,
            $this->request('POST', '/api/v1/form/cargo-account-registration'),
            function ($req, $res) {
                return $res;
            }
        );

        $body = json_decode((string)$response->getBody(), true);
        $this->assertEquals('Please try submitting the form again.', $body['message']);
    }

    public function testFailureBodyTemplateSupportsNestedShapes()
    {
        $mw = new CsrfMiddleware([
            'guard'           => new Guard(),
            'included_path'   => [ '^/admin/login$' ],
            'failure_message' => 'Your session has expired. Please try logging in again.',
            'failure_body'    => [
                'success'   => false,
                'next_url'  => null,
                'feedbacks' => [
                    [ 'level' => 'error', 'message' => '{{message}}' ],
                ],
            ],
        ]);

        $response = $this->invoke(
            $mw,
            $this->request('POST', '/admin/login'),
            function ($req, $res) {
                return $res;
            }
        );

        $body = json_decode((string)$response->getBody(), true);
        $this->assertSame(false, $body['success']);
        $this->assertNull($body['next_url']);
        $this->assertEquals(
            [ [ 'level' => 'error', 'message' => 'Your session has expired. Please try logging in again.' ] ],
            $body['feedbacks']
        );
    }

    /**
     * @param  string $method The HTTP method.
     * @param  string $path   The request path.
     * @param  array  $body   The parsed body, for POST/PUT/DELETE/PATCH requests.
     * @return Request
     */
    private function request(string $method, string $path, array $body = []): Request
    {
        $request = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI'    => $path,
        ]));

        if (!empty($body)) {
            $request = $request->withParsedBody($body);
        }

        return $request;
    }

    /**
     * @param  CsrfMiddleware $middleware The middleware under test.
     * @param  Request        $request    The PSR-7 request to send through it.
     * @param  callable       $next       The next middleware callable in the stack.
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function invoke(CsrfMiddleware $middleware, Request $request, callable $next)
    {
        return $middleware($request, new Response(), $next);
    }
}
