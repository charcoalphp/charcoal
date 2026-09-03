<?php

namespace Charcoal\Tests\App\Helper;

use Charcoal\App\Helper\CallbackStream;
use Charcoal\Tests\AbstractTestCase;

class CallbackStreamTest extends AbstractTestCase
{
    public function testOutputInvokesCallbackOnce()
    {
        $calls = 0;
        $stream = new CallbackStream(function () use (&$calls) {
            $calls++;
            return 'payload';
        });

        $this->assertFalse($stream->eof());
        $this->assertSame('payload', $stream->output());
        $this->assertTrue($stream->eof());
        $this->assertNull($stream->output());
        $this->assertSame(1, $calls);
    }

    public function testReadAndGetContentsDelegateToOutput()
    {
        $stream = new CallbackStream(function () {
            return 'once';
        });

        $this->assertSame('once', $stream->read(10));
        $this->assertSame('', $stream->getContents());
    }

    public function testToStringUsesOutput()
    {
        $stream = new CallbackStream(function () {
            return 'cast';
        });

        $this->assertSame('cast', (string)$stream);
        $this->assertSame('', (string)$stream);
    }

    public function testDetachClearsCallback()
    {
        $callback = function () {
            return 'x';
        };
        $stream = new CallbackStream($callback);

        $this->assertSame($callback, $stream->detach());
        $this->assertNull($stream->detach());
    }

    public function testStreamCapabilities()
    {
        $stream = new CallbackStream(function () {
            return 'x';
        });

        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->seek(1));
        $this->assertFalse($stream->rewind());
        $this->assertFalse($stream->write('nope'));
        $this->assertNull($stream->getSize());
        $this->assertSame(0, $stream->tell());
        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('uri'));

        $stream->close();
        $this->assertTrue(true);
    }
}
