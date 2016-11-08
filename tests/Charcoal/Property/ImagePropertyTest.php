<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\ImageProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class ImagePropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ImageProperty([
            'logger' => new NullLogger()
        ]);
    }

    public function testType()
    {
        $this->assertEquals('image', $this->obj->type());
    }

    public function testSetEffects()
    {
        $this->assertEquals([], $this->obj->effects());
        $ret = $this->obj->setEffects([['type'=>'blur', 'sigma'=>'1']]);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(1, count($this->obj->effects()));
    }

    public function testAddEffect()
    {
        $this->assertEquals(0, count($this->obj->effects()));

        $ret = $this->obj->addEffect(['type'=>'grayscale']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(1, count($this->obj->effects()));

        $this->obj->addEffect(['type'=>'blur', 'sigma'=>1]);
        $this->assertEquals(2, count($this->obj->effects()));
    }
}
