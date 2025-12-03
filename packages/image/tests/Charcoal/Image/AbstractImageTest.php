<?php

namespace Charcoals\Tests\Image;

use Charcoal\Image\AbstractImage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractImage::class)]
class AbstractImageTest extends \PHPUnit\Framework\TestCase
{
    public function testSetData()
    {
        /** @var AbstractImage $obj */
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->setData(
            [
                'source' => __DIR__ . '/test.png',
                'target' => '/tmp/phpunit.png',
                'effects' => []
            ]
        );
        $this->assertSame($ret, $obj);

        $this->assertEquals(__DIR__ . '/test.png', $obj->source());
        $this->assertEquals('/tmp/phpunit.png', $obj->target());
    }

    public function testSetSource()
    {
        /** @var AbstractImage $obj */
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->setSource('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->source());

        $this->expectException(InvalidArgumentException::class);
        $obj->setSource(false);
    }

    public function testSetTarget()
    {
        /** @var AbstractImage $obj */
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->setTarget('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->target());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTarget(false);
    }
}
