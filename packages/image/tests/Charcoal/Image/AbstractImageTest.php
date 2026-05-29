<?php

namespace Charcoal\Tests\Image;

use Charcoal\Tests\Mock\ImageMock;
use InvalidArgumentException;

class AbstractImageTest extends \PHPUnit\Framework\TestCase
{
    public function testSetData(): void
    {
        $obj = new ImageMock();
        $ret = $obj->setData(
            [
            'source'=>__DIR__.'/test.png',
            'target'=>'/tmp/phpunit.png',
            'effects'=>[

            ]
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(__DIR__.'/test.png', $obj->source());
        $this->assertEquals('/tmp/phpunit.png', $obj->target());
    }

    public function testSetSource(): void
    {
        $obj = new ImageMock();
        $ret = $obj->setSource('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->source());

        $this->expectException(InvalidArgumentException::class);
        $obj->setSource(false);
    }

    public function testSetTarget(): void
    {
        $obj = new ImageMock();
        $ret = $obj->setTarget('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->target());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTarget(false);
    }
}
