<?php

namespace Charcoal\Tests\Property;

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

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new SpriteProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testDefaults()
    {
        $this->assertNull($this->obj->sprite());
    }


    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('sprite', $this->obj->type());
    }


    public function testSetSprite()
    {
        $this->assertNull($this->obj->sprite());
        $ret = $this->obj->setSprite('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->sprite());
    }

    public function testBuildChoices()
    {
        $ret = $this->obj->buildChoicesFromSprite();
        $this->assertEmpty($ret);

        $this->obj->setSprite('composer.json');
        $ret = $this->obj->buildChoicesFromSprite();
        var_dump($ret);
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());
        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(    \PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
