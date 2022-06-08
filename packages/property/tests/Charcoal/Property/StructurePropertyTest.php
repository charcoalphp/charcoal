<?php

namespace Charcoal\Tests\Property;

use Exception;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\StructureProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class StructurePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var StructureProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new StructureProperty([
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
        $this->assertEquals('structure', $this->obj->type());
    }

    public function testSetL10nThrowsException()
    {
        $this->assertFalse($this->obj['l10n']);
        $ret = $this->obj->setL10n(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj['l10n']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setL10n(true);
    }

    public function testParseOneNull()
    {
        $this->obj->setAllowNull(true);
        $this->assertNull($this->obj->parseOne(null));

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->parseOne(null);
    }

    public function testParseOneString()
    {
        $this->assertEquals('', $this->obj->parseOne(''));
       // $this->assertEquals('foo', $this->obj->parseOne('foo'));
        $this->assertEquals(['foo'], $this->obj->parseOne('["foo"]'));
        $this->assertEquals(['foo'=>'bar'], $this->obj->parseOne('{"foo":"bar"}'));
    }

    public function testSqlType()
    {
        $this->assertEquals('TEXT', $this->obj->sqlType());

        $ret = $this->obj->setSqlType('LONGTEXT');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('LONGTEXT', $this->obj->sqlType());

        $this->obj->setSqlType('long');
        $this->assertEquals('LONGTEXT', $this->obj->sqlType());

        $this->obj->setSqlType('medium');
        $this->assertEquals('MEDIUMTEXT', $this->obj->sqlType());

        $this->obj->setSqlType('TINY');
        $this->assertEquals('TINYTEXT', $this->obj->sqlType());

        $this->obj->setSqlType('TEXT');
        $this->assertEquals('TEXT', $this->obj->sqlType());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlType('foobar');
    }

    public function testSetSqlTypeNullException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlType(false);
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testInputVal()
    {
        $this->assertEquals('', $this->obj->inputVal(''));
        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('{}', $this->obj->inputVal(new \StdClass()));
        $this->assertEquals('[]', $this->obj->inputVal([]));
    }

    public function testStorageVal()
    {
        $this->assertEquals('', $this->obj->inputVal(''));
        $this->assertEquals(null, $this->obj->inputVal(null));
        $this->assertEquals('{}', $this->obj->inputVal(new \StdClass()));
        $this->assertEquals('[]', $this->obj->inputVal([]));
    }
}
