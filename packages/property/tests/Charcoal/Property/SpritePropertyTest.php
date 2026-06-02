<?php

namespace Charcoal\Tests\Property;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\SpriteProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class SpritePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var SpriteProperty
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $container['view'] = null;

        $this->obj = new SpriteProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
            'container'  => $container
        ]);
    }

    public function testDefaults(): void
    {
        $this->assertNull($this->obj['sprite']);
    }

    public function testType(): void
    {
        $this->assertEquals('sprite', $this->obj->type());
    }

    public function testSetSprite(): void
    {
        $this->assertNull($this->obj['sprite']);
        $ret = $this->obj->setSprite('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['sprite']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSprite(false);
    }

    public function testBuildChoices(): void
    {
        $ret = $this->obj->buildChoicesFromSprite();
        $this->assertEquals([], $ret);

        $this->obj->setSprite('composer.json');
        $ret = $this->obj->buildChoicesFromSprite();
        $this->assertEquals([], $ret);
    }

    public function testSqlExtra(): void
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType(): void
    {
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType(): void
    {
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
