<?php

namespace Charcoal\Tests\Property;

use PDO;

// From 'charcoal-property'
use Charcoal\Property\IpProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class IpPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var IpProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new IpProperty([
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
        $this->assertEquals('ip', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testDefaults()
    {
        $this->assertEquals('string', $this->obj['storageMode']);
    }

    /**
     * @return void
     */
    public function testMultipleCannotBeTrue()
    {
        $this->assertFalse($this->obj['multiple']);

        $this->assertSame($this->obj, $this->obj->setMultiple(false));
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setMultiple(true);
    }

    /**
     * @return void
     */
    public function testL10nCannotBeTrue()
    {
        $this->assertFalse($this->obj['l10n']);

        $this->assertSame($this->obj, $this->obj->setL10n(false));
        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setL10n(true);
    }

    /**
     * @return void
     */
    public function testSetStorageMode()
    {
        $this->assertEquals('string', $this->obj['storageMode']);
        $ret = $this->obj->setStorageMode('int');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('int', $this->obj['storageMode']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setStorageMode('foobar');
    }

    /**
     * @return void
     */
    public function testIntVal()
    {
        $this->assertEquals(0, $this->obj->intVal('0.0.0.0'));
        $this->assertEquals(2130706433, $this->obj->intVal('127.0.0.1'));
        $this->assertEquals(3232235777, $this->obj->intVal('192.168.1.1'));
        $this->assertEquals(3232235777, $this->obj->intVal(3232235777));
        $this->assertEquals(3232235777, $this->obj->intVal('3232235777'));
    }

    /**
     * @return void
     */
    public function testStringVal()
    {
        $this->assertEquals('0.0.0.0', $this->obj->stringVal(0));
        $this->assertEquals('127.0.0.1', $this->obj->stringVal(2130706433));
        $this->assertEquals('192.168.1.1', $this->obj->stringVal(3232235777));
        $this->assertEquals('8.8.8.8', $this->obj->stringVal('8.8.8.8'));
    }

    public function testStorageVal()
    {
        $this->assertEquals('0.0.0.0', $this->obj->storageVal('0.0.0.0'));
        $this->assertEquals('127.0.0.1', $this->obj->storageVal('127.0.0.1'));
        $this->assertEquals('127.0.0.1', $this->obj->stringVal('127.0.0.1'));
    }

    public function testHostname()
    {
        $this->assertEquals('0.0.0.0', $this->obj->hostname(0));
        $this->assertThat($this->obj->hostname('8.8.8.8'), $this->logicalOr(
            $this->identicalTo('dns.google'),
            $this->identicalTo('google.com')
        ));
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    /**
     * Asserts that the `sqlType()` method:
     * - returns "VARCHAR(15)" if the storage mode is "string" (default).
     * - returns "BIGINT" if the storage mode is "int".
     *
     * @return void
     */
    public function testSqlType()
    {
        $this->obj->setStorageMode('string');
        $this->assertEquals('VARCHAR(15)', $this->obj->sqlType());

        $this->obj->setStorageMode('int');
        $this->assertEquals('BIGINT', $this->obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->obj->setStorageMode('string');
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());

        $this->obj->setStorageMode('int');
        $this->assertEquals(PDO::PARAM_INT, $this->obj->sqlPdoType());
    }
}
