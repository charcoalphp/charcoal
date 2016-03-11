<?php

namespace Charcoals\Tests\Image;

class AbstractImageTest extends \PHPUnit_Framework_Testcase
{

    public function testSetData()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
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

    public function testSetSource()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->setSource('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->source());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSource(false);
    }

    public function testSetTarget()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->setTarget('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->target());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setTarget(false);
    }
}
