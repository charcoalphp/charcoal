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

    /**
     * @return void
     */
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

    public function testDefaults()
    {
        $this->assertNull($this->obj['sprite']);
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
        $this->assertNull($this->obj['sprite']);
        $ret = $this->obj->setSprite('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['sprite']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSprite(false);
    }

    public function testBuildChoices()
    {
        $ret = $this->obj->buildChoicesFromSprite();
        $this->assertEquals([], $ret);

        $this->obj->setSprite('composer.json');
        $ret = $this->obj->buildChoicesFromSprite();
        $this->assertEquals([], $ret);
    }

    public function testDisplayValEscapesLabel()
    {
        $container = $this->getContainer();
        $this->getContainerProvider()->registerView($container);

        $obj = new SpriteProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
            'container'  => $container
        ]);
        $obj->setSprite('foo.svg');

        $html = $obj->displayVal('bar');

        $this->assertStringContainsString('aria-label="Selected sprite icon &quot;bar&quot;"', $html);
        $this->assertStringNotContainsString('aria-label="Selected sprite icon "bar""', $html);
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
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
