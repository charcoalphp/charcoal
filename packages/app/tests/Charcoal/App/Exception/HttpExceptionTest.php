<?php

namespace Charcoal\Tests\App\Exception;

use InvalidArgumentException;
// From Slim
use Slim\Http\Response;
// From 'charcoal-app'
use Charcoal\App\Exception\HttpException;
use Charcoal\Tests\AbstractTestCase;

/**
 * @covers \Charcoal\App\Exception\HttpException
 */
class HttpExceptionTest extends AbstractTestCase
{
    public function testStatusAndHeadersAreAppliedToResponse()
    {
        $exception = new HttpException(403, [
            'Location'       => '/admin/login',
            'X-Test-Header'  => 'yes'
        ]);

        $response = $exception->toResponse(new Response());

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('/admin/login', $response->getHeaderLine('Location'));
        $this->assertSame('yes', $response->getHeaderLine('X-Test-Header'));
    }

    public function testStatusCodeIsExposed()
    {
        $exception = new HttpException(400);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertSame(400, $exception->getCode());
        $this->assertSame([], $exception->getHeaders());
    }

    public function testInvalidStatusCodeIsRejected()
    {
        $this->expectException(InvalidArgumentException::class);

        new HttpException(99);
    }
}
