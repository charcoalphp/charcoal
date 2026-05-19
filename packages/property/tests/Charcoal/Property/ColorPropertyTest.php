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

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new ColorProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('color', $this->obj->type());
    }

    public function testParseOneNull(): void
    {
        $this->obj->setAllowNull(true);
        $this->assertNull($this->obj->parseOne(null));

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->parseOne(null);
    }

    public function testParseOneEmpty(): void
    {
        $this->obj->setAllowNull(true);
        $this->assertNull($this->obj->parseOne(''));

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->parseOne('');
    }

    public function parseOneFalse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->parseOne(false);
    }

    public function parseOneArray(): void
    {
        $this->assertEquals(['r'=>255, 'g'=>255, 'b'=>255], $this->obj->parseOne([255,255,255]));
        $this->expectException(InvalidArgumentException::class);
        $this->obj->parseOne([255]);
    }

    /**
     * Hello world
     */
    public function testDefaults(): void
    {
        $this->assertEquals(false, $this->obj['supportAlpha']);
    }

    public function testSetSupportAlpha(): void
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
     *
     * @param  string $color    A color to test.
     * @param  string $expected The expected mutation of $color.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('colorProviderNoAlpha')]
    public function testColorValueNoAlpha(string|array $color, string $expected): void
    {
        $this->obj->setSupportAlpha(false);
        $this->assertEquals($expected, $this->obj->colorVal($color));
    }

    /**
     *
     * @param  string $color    A color to test.
     * @param  string $expected The expected mutation of $color.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('colorProviderAlpha')]
    public function testColorValueAlpha(string|array $color, string $expected): void
    {
        $this->obj->setSupportAlpha(true);
        $this->assertEquals($expected, $this->obj->colorVal($color));
    }

    public function testColorValInvalidThrowsException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->colorVal('invalid');
    }

    /**
     * Provider for hexadcimalValue, in `[$color, $expected]` pairs.
     */
    public static function colorProviderNoAlpha(): array
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
     */
    public static function colorProviderAlpha(): array
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

    public function testSqlExtra(): void
    {
        $obj = $this->obj;
        $this->assertEquals('', $obj->sqlExtra());
    }

    public function testSqlTypeMultiple(): void
    {
        $obj = $this->obj;
        $obj->setMultiple(true);
        $this->assertEquals('TEXT', $obj->sqlType());
    }

    public function testSqlType(): void
    {
        $obj = $this->obj;

        $obj->setSupportAlpha(true);
        $this->assertEquals('VARCHAR(32)', $obj->sqlType());

        $obj->setSupportAlpha(false);
        $this->assertEquals('CHAR(7)', $obj->sqlType());
    }

    public function testSqlPdoType(): void
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
