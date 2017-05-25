<?php

namespace Charcoal\Tests\Property;

use PDO;

// From 'charcoal-property'
use Charcoal\Property\IdProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class IdPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var IdProperty
     */
    private $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new IdProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('id', $this->obj->type());
    }


    public function testSetData()
    {
        $ret = $this->obj->setData(
            [
            'mode'=>'uniqid'
            ]
        );
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('uniqid', $this->obj->mode());
    }

    /**
     * Asserts that the default mode:
     * - Defaults to auto-increment
     */
    public function testDefaultMode()
    {
        $this->assertEquals(IdProperty::DEFAULT_MODE, $this->obj->mode());
        $this->assertEquals('auto-increment', $this->obj->mode());
    }

    /**
     * Asserts that the `setMode` method:
     * - is chainable
     * - properly sets the mode
     * - throws an invalid argument exception for any string modes
     */
    public function testSetMode()
    {
        $ret = $this->obj->setMode('uuid');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('uuid', $this->obj->mode());

        $this->obj['mode'] = 'auto-increment';
        $this->assertEquals('auto-increment', $this->obj->mode());

        $this->obj->set('mode', 'uniqid');
        $this->assertEquals('uniqid', $this->obj['mode']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMode('foo');
    }

    /**
     * Asserts that calling the `setMode()` method with a NULL argument:
     * - is chainable
     * - properly resets the mode to detault
     */
    public function testSetModeNull()
    {
        $ret = $this->obj->setMode(null);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(IdProperty::DEFAULT_MODE, $this->obj->mode());
    }

    public function testMultipleCannotBeTrue()
    {
        $this->assertFalse($this->obj->multiple());

        $this->assertSame($this->obj, $this->obj->setMultiple(false));
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMultiple(true);
    }

    public function testL10nCannotBeTrue()
    {
        $this->assertFalse($this->obj->l10n());

        $this->assertSame($this->obj, $this->obj->setL10n(false));
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setL10n(true);
    }

    public function testSaveAndAutoGenerateAutoIncrement()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $id = $obj->save(null);
        $this->assertEquals('', $id);
    }

    public function testSaveAndAutoGenerateUniqid()
    {
        $obj = $this->obj;
        $obj->setMode('uniqid');
        $id = $obj->save(null);
        $this->assertEquals(13, strlen($id));
    }

    public function testSaveAndAutoGenerateUuid()
    {
        $obj = $this->obj;
        $obj->setMode('uuid');
        $id = $obj->save(null);
        $this->assertEquals(36, strlen($id));
    }

    public function testSqlExtra()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlExtra();
        $this->assertEquals('AUTO_INCREMENT', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlExtra();
        $this->assertEquals('', $ret);
    }

    public function testSqlType()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlType();
        //$this->assertEquals('INT(10) UNSIGNED', $ret);
        $this->assertEquals('INT', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(13)', $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(36)', $ret);
    }

    public function testSqlPdoType()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_INT, $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_STR, $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_STR, $ret);
    }
}
