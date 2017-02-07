<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use ReflectionClass;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\ColorProperty;

/**
 *
 */
class ColorPropertyTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new ColorProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    protected static function callMethod($obj, $name, array $args = null)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Hello world
     */
    public function testDefaults()
    {
        $this->assertEquals(false, $this->obj->supportAlpha());
    }

    public function testSetSupportAlpha()
    {
        $ret = $this->obj->setSupportAlpha(true);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(true, $this->obj->supportAlpha());

        $this->obj->setSupportAlpha(0);
        $this->assertFalse($this->obj->supportAlpha());

        $this->obj['support_alpha'] = true;
        $this->assertTrue($this->obj->supportAlpha());

        $this->obj->set('support_alpha', false);
        $this->assertFalse($this->obj['support_alpha']);
    }

    /**
     * @dataProvider colorProviderNoAlpha
     */
    public function testColorValueNoAlpha($color, $result)
    {
        $this->obj->setSupportAlpha(false);
        $this->assertEquals($result, $this->obj->colorVal($color));
    }

    /**
     * @dataProvider colorProviderAlpha
     */
    public function testColorValueAlpha($color, $result)
    {
        $this->obj->setSupportAlpha(true);
        $this->assertEquals($result, $this->obj->colorVal($color));
    }

    public function testColorValInvalidThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->colorVal('invalid');
    }

    /**
     * Provider for hexadcimalValue, in `[$color, $result]` pairs.
     */
    public function colorProviderNoAlpha()
    {
        return [
            ['#FF00FF', '#FF00FF'],
            ['#ab98ab', '#AB98AB'],
            ['rgb(255,0,255)', '#FF00FF'],
            ['rgb(255, 0, 255)', '#FF00FF'],
            //['rgb(100%,0%,100%)', 'FF00FF'],
            ['FF00FF', '#FF00FF'],
            //['#F0F', 'FF00FF'],
            ['fuchsia', '#FF00FF'],
            ['CornFlowerBlue', '#6495ED'],
            ['Red', '#FF0000'],
            ['RED', '#FF0000'],
            [[255,0,255], '#FF00FF'],
            [['r'=>255, 'g'=>0, 'b'=>255], '#FF00FF'],
            [['r'=>255, 'g'=>0, 'b'=>255, 'a'=>0], '#FF00FF']
        ];
    }

        /**
         * Provider for hexadcimalValue, in `[$color, $result]` pairs.
         */
    public function colorProviderAlpha()
    {
        return [
            ['#FF00FF', 'rgba(255,0,255,0)'],
            ['#ab98ab', 'rgba(171,152,171,0)'],
            ['rgb(255,0,255)', 'rgba(255,0,255,0)'],
            ['rgb(255, 0, 255)', 'rgba(255,0,255,0)'],
            //['rgb(100%,0%,100%)', 'FF00FF'],
            ['FF00FF', 'rgba(255,0,255,0)'],
            //['#F0F', 'FF00FF'],
            ['fuchsia', 'rgba(255,0,255,0)'],
            ['CornFlowerBlue', 'rgba(100,149,237,0)'],
            ['Red', 'rgba(255,0,0,0)'],
            ['RED', 'rgba(255,0,0,0)'],
            [[255,0,255], 'rgba(255,0,255,0)'],
            [['r'=>255, 'g'=>0, 'b'=>255], 'rgba(255,0,255,0)'],
            [['r'=>255, 'g'=>0, 'b'=>255, 'a'=>0], 'rgba(255,0,255,0)']
        ];
    }

    public function testSqlExtra()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->sqlExtra());
    }

    public function testSqlTypeMultiple()
    {
        $obj = $this->obj;
        $obj->setMultiple(true);
        $this->assertEquals('TEXT', $obj->sqlType());
    }

    public function testSqlType()
    {
        $obj = $this->obj;

        $obj->setSupportAlpha(true);
        $this->assertEquals('VARCHAR(32)', $obj->sqlType());

        $obj->setSupportAlpha(false);
        $this->assertEquals('CHAR(7)', $obj->sqlType());
    }
}
