<?php

namespace Charcoals\Tests\Image;

class AbstractImageTest extends \PHPUnit_Framework_Testcase
{

    public function testSetData()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->set_data(
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
        $ret = $obj->set_source('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->source());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_source(false);
    }

    public function testSetTarget()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Image\AbstractImage');
        $ret = $obj->set_target('test.png');
        $this->assertSame($ret, $obj);
        $this->assertEquals('test.png', $obj->target());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_target(false);
    }
}
