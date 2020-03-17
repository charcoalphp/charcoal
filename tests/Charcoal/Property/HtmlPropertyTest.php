<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\HtmlProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class HtmlPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var HtmlProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new HtmlProperty([
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
        $this->assertEquals('html', $this->obj->type());
    }

    public function testDefaults()
    {
        $this->assertFalse($this->obj['required']);
        $this->assertFalse($this->obj['unique']);
        $this->assertTrue($this->obj['storable']);
        $this->assertFalse($this->obj['l10n']);
        $this->assertFalse($this->obj['multiple']);
        $this->assertTrue($this->obj['allowNull']);
        $this->assertTrue($this->obj['allowHtml']);
        $this->assertTrue($this->obj['active']);
        $this->assertTrue($this->obj['long']);
    }

    /**
     * @return void
     */
    public function testDefaultMaxLength()
    {
        $this->assertEquals(0, $this->obj['maxLength']);
        $this->assertEquals(0, $this->obj->defaultMaxLength());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $this->obj->setLong(false);
        $this->assertEquals('TEXT', $this->obj->sqlType());

        $this->obj->setLong(true);
        $this->assertEquals('LONGTEXT', $this->obj->sqlType());
    }

    public function testFilesystem()
    {
        $this->assertEquals('', $this->obj['filesystem']);

        $ret = $this->obj->setFilesystem('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['filesystem']);
    }
}
