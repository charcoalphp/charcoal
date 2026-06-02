<?php

declare(strict_types=1);

namespace Charcoal\Tests\Property;

use Charcoal\Property\TextProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class TextPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var TextProperty
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new TextProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Asserts that the `type()` method returns "text".
     */
    public function testType(): void
    {
        $this->assertEquals('text', $this->obj->type());
    }

    public function testDefaults(): void
    {
        $this->assertFalse($this->obj['required']);
        $this->assertFalse($this->obj['unique']);
        $this->assertTrue($this->obj['storable']);
        $this->assertFalse($this->obj['l10n']);
        $this->assertFalse($this->obj['multiple']);
        $this->assertTrue($this->obj['allowNull']);
        $this->assertFalse($this->obj['allowHtml']);
        $this->assertTrue($this->obj['active']);
        $this->assertFalse($this->obj['long']);
    }

    /**
     * Asserts that the `defaultMaxLength` method returns 0 (no limit).
     */
    public function testDefaultMaxLength(): void
    {
        $this->assertEquals(0, $this->obj->defaultMaxLength());
    }

    /**
     * Asserts that the `sqlType()` method returns "TEXT".
     */
    public function testSqlType(): void
    {
        $this->assertEquals('TEXT', $this->obj->sqlType());

        $this->obj->setLong(true);
        $this->assertEquals('LONGTEXT', $this->obj->sqlType());
    }
}
