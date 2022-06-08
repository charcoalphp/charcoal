<?php

namespace Charcoal\Tests\Property;

use Exception;
use InvalidArgumentException;
use PDO;
use ReflectionClass;

// From 'charcoal-property'
use Charcoal\Property\ColorProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ColorPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var ColorProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new ColorProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('color', $this->obj->type());
    }

    public function testParseOneNull()
    {
        $this->obj->setAllowNull(true);
        $this->assertNull($this->obj->parseOne(null));

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->parseOne(null);
    }

    public function testParseOneEmpty()
    {
        $this->obj->setAllowNull(true);
        $this->assertNull($this->obj->parseOne(''));

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->parseOne('');
    }

    public function parseOneFalse()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->parseOne(false);
    }

    public function parseOneArray()
    {
        $this->assertEquals(['r'=>255, 'g'=>255, 'b'=>255], $this->obj->parseOne([255,255,255]));
        $this->expectException(InvalidArgumentException::class);
        $this->obj->parseOne([255]);
    }

    /**
     * Hello world
     *
     * @return void
     */
    public function testDefaults()
    {
        $this->assertEquals(false, $this->obj['supportAlpha']);
    }

    /**
     * @return void
     */
    public function testSetSupportAlpha()
    {
        $ret = $this->obj->setSupportAlpha(true);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(true, $this->obj['supportAlpha']);

        $this->obj->setSupportAlpha(0);
        $this->assertFalse($this->obj['supportAlpha']);

        $this->obj['support_alpha'] = true;
        $this->assertTrue($this->obj['supportAlpha']);

        $this->obj->set('support_alpha', false);
        $this->assertFalse($this->obj['supportAlpha']);
    }

    /**
     * @dataProvider colorProviderNoAlpha
     *
     * @param  string $color    A color to test.
     * @param  string $expected The expected mutation of $color.
     * @return void
     */
    public function testColorValueNoAlpha($color, $expected)
    {
        $this->obj->setSupportAlpha(false);
        $this->assertEquals($expected, $this->obj->colorVal($color));
    }

    /**
     * @dataProvider colorProviderAlpha
     *
     * @param  string $color    A color to test.
     * @param  string $expected The expected mutation of $color.
     * @return void
     */
    public function testColorValueAlpha($color, $expected)
    {
        $this->obj->setSupportAlpha(true);
        $this->assertEquals($expected, $this->obj->colorVal($color));
    }

    /**
     * @return void
     */
    public function testColorValInvalidThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->colorVal('invalid');
    }

    /**
     * Provider for hexadcimalValue, in `[$color, $expected]` pairs.
     *
     * @return array
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
            [['r'=>255, 'g'=>0, 'b'=>255, 'a'=>0], '#FF00FF'],
            ['ABC', '#AABBCC']
        ];
    }

    /**
     * Provider for hexadcimalValue, in `[$color, $result]` pairs.
     *
     * @return array
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

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->sqlExtra());
    }

    /**
     * @return void
     */
    public function testSqlTypeMultiple()
    {
        $obj = $this->obj;
        $obj->setMultiple(true);
        $this->assertEquals('TEXT', $obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $obj = $this->obj;

        $obj->setSupportAlpha(true);
        $this->assertEquals('VARCHAR(32)', $obj->sqlType());

        $obj->setSupportAlpha(false);
        $this->assertEquals('CHAR(7)', $obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
