<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Model\Source\Database as Source;
use \Charcoal\Model\Model as Model;
use \Charcoal\Model\Object as Object;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        Charcoal::config()->add_database('unit_test', [
            'username'=>'root',
            'password'=>'',
            'database'=>'charcoal_examples'
        ]);

        Charcoal::config()->set_default_database('unit_test');

        $obj = new Source();
        //$obj->set_model($model);
        $obj->set_table('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $obj->db()->query($q);
    }

    public function testConstructor()
    {
        $obj = new Source();
        $this->assertInstanceOf('\Charcoal\Model\Source\Database', $obj);

    }

    public function testTableWithoutSetterThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new Source();
        $obj->table();
    }

    public function testSetTable()
    {
        $obj = new Source();
        $obj->set_table('foo');
        $this->assertEquals('foo', $obj->table());
    }

    public function testSetTableIsChainable()
    {
        $obj = new Source();
        $ret = $obj->set_table('foo');
        $this->assertSame($ret, $obj);
    }

    public function testSetTableInvalidArgumentThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new Source();
        $obj->set_table(null);
    }

    public function testCreateTableWithoutTableThrowsException()
    {
        $this->setExpectedException('\Exception');
        $obj = new Source();
        $obj->create_table();
    }

    public function testCreateTableWithoutModelThrowsException()
    {
        $this->setExpectedException('\Exception');
        $obj = new Source();
        $obj->set_table('foo');
        $obj->create_table();
    }

    public function testCreateTable()
    {
        $model = new Object();
        $model->set_metadata([
            'properties'=>[
                'id'=>[
                    'type'=>'id'
                ]
            ]
        ]);

        $obj = new Source();
        $obj->set_model($model);
        $obj->set_table('test');
        $this->assertNotTrue($obj->table_exists());

        $ret = $obj->create_table();
        $this->assertTrue($ret);
        $this->assertTrue($obj->table_exists());
    }

    public function testCreateTableTableExistsReturnsTrue()
    {
        $obj = new Source();
        //$obj->set_model($model);
        $obj->set_table('test');
        $this->assertTrue($obj->table_exists());

        $ret = $obj->create_table();
        $this->assertTrue($ret);
    }

    public function testCreateAlterWithoutModelThrowsException()
    {
        $this->setExpectedException('\Exception');
        
        $obj = new Source();
        $obj->set_table('test');

        $this->assertTrue($obj->table_exists());
        $obj->alter_table();
    }

    public function testAlterTableNewProperty()
    {
        $model = new Object();
        $model->set_metadata([
            'properties'=>[
                'id'=>[
                    'type'=>'id'
                ],
                'name'=>[
                    'type'=>'string',
                    'max_length'=>120
                ]
            ]
        ]);

        $obj = new Source();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->alter_table();

        $this->assertTrue($ret);
    }

    public function testAlterTablePropertyChange()
    {
        $model = new Object();
        $model->set_metadata([
            'properties'=>[
                'id'=>[
                    'type'=>'id',

                ],
                'name'=>[
                    'type'=>'string',
                    'max_length'=>300
                ]
            ]
        ]);

        $obj = new Source();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->alter_table();

        $this->assertTrue($ret);
    }
}
