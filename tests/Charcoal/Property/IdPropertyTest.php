<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\IdProperty as IdProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class IdPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new IdProperty();
        $this->assertInstanceOf('\Charcoal\Property\IdProperty', $obj);
    }

    public function testSetData()
    {
        $obj = new IdProperty();
        $ret = $obj->setData(
            [
            'mode'=>'uniqid'
            ]
        );
        $this->assertSame($ret, $obj);
        $this->assertEquals('uniqid', $obj->mode());
    }

    public function testSetMode()
    {
        $obj = new IdProperty();
        $this->assertEquals('auto-increment', $obj->mode());

        $ret = $obj->setMode('uuid');
        $this->assertSame($ret, $obj);
        $this->assertEquals('uuid', $obj->mode());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMode('foo');
    }

    public function testSaveAndAutoGenerate()
    {
        $obj = new IdProperty();
        $obj->setMode('auto-increment');
        $id = $obj->save();
        $this->assertEquals($id, $obj->val());
        $this->assertEquals('', $obj->val());

        $obj = new IdProperty();
        $obj->setMode('uniqid');
        $id = $obj->save();
        $this->assertEquals($id, $obj->val());
        $this->assertEquals(13, strlen($obj->val()));

        $obj = new IdProperty();
        $obj->setMode('uuid');
        $id = $obj->save();
        $this->assertEquals($id, $obj->val());
        $this->assertEquals(36, strlen($obj->val()));
    }

    public function testSqlExtra()
    {
        $obj = new IdProperty();
        $obj->setMode('auto-increment');
        $ret = $obj->sqlExtra();
        $this->assertEquals('AUTO_INCREMENT', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlExtra();
        $this->assertEquals('', $ret);
    }

    public function testSqlType()
    {
        $obj = new IdProperty();
        $obj->setMode('auto-increment');
        $ret = $obj->sqlType();
        $this->assertEquals('INT(10) UNSIGNED', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(13)', $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(36)', $ret);
    }

    public function testSqlPdoType()
    {
        $obj = new IdProperty();
        $obj->setMode('auto-increment');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(\PDO::PARAM_INT, $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(\PDO::PARAM_STR, $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(\PDO::PARAM_STR, $ret);
    }
}
