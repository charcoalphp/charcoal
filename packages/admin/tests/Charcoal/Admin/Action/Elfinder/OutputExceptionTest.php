<?php

namespace Charcoal\Tests\Admin\Action\Elfinder;

// From Slim
use Slim\Http\Response;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Elfinder\OutputException;
use Charcoal\Tests\AbstractTestCase;

/**
 * @covers \Charcoal\Admin\Action\Elfinder\OutputException
 */
class OutputExceptionTest extends AbstractTestCase
{
    public function testJsonOutputIsConvertedToResponse()
    {
        $exception = new OutputException([ 'foo' => 'bar' ]);

        $response = $exception->toResponse(new Response());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"foo":"bar"}', (string)$response->getBody());
        $this->assertSame((string)strlen('{"foo":"bar"}'), $response->getHeaderLine('Content-Length'));
    }

    public function testRawErrorAndStatusHeaderArePreserved()
    {
        $exception = new OutputException([
            'raw'    => true,
            'error'  => 'File not found',
            'header' => 'HTTP/1.1 404 Not Found'
        ]);

        $response = $exception->toResponse(new Response());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('File not found', (string)$response->getBody());
    }

    public function testFileOutputIsStreamedToResponse()
    {
        $pointer = fopen('php://temp', 'r+');
        fwrite($pointer, 'hello world');
        rewind($pointer);

        $exception = new OutputException([
            'pointer' => $pointer,
            'info'    => [
                'size' => 11,
                'hash' => 'abc'
            ]
        ]);

        $response = $exception->toResponse(new Response());

        $this->assertSame('11', $response->getHeaderLine('Content-Length'));
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testFileRangeIsHonored()
    {
        $pointer = fopen('php://temp', 'r+');
        fwrite($pointer, 'hello world');
        rewind($pointer);

        $_SERVER['HTTP_RANGE'] = 'bytes=6-10';

        try {
            $exception = new OutputException([
                'pointer' => $pointer,
                'info'    => [
                    'size' => 11,
                    'hash' => 'abc'
                ]
            ]);

            $response = $exception->toResponse(new Response());

            $this->assertSame(206, $response->getStatusCode());
            $this->assertSame('bytes 6-10/11', $response->getHeaderLine('Content-Range'));
            $this->assertSame('5', $response->getHeaderLine('Content-Length'));
            $this->assertSame('world', (string)$response->getBody());
        } finally {
            unset($_SERVER['HTTP_RANGE']);
        }
    }
}
