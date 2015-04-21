<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Source\DatabaseSource as DatabaseSource;

use \Charcoal\Model\Object as Object;

class DatabaseSourceTest extends \PHPUnit_Framework_TestCase
{
    static public function setUpBeforeClass()
    {
        Charcoal::config()->add_database('unit_test', [
            'username'=>'root',
            'password'=>'',
            'database'=>'charcoal_examples'
        ]);

        Charcoal::config()->set_default_database('unit_test');

        $obj = new DatabaseSource();
        //$obj->set_model($model);
        $obj->set_table('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $obj->db()->query($q);
    }

    public function testTableWithoutSetterThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new DatabaseSource();
        $obj->table();
    }

    public function testSetDatabaseIdent()
    {
        $obj = new DatabaseSource();
        $ret = $obj->set_database_ident('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->database_ident());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_database_ident(null);
    }

    public function testSetTable()
    {
        $obj = new DatabaseSource();
        $ret = $obj->set_table('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->table());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_table(null);
    }

    public function testCreateTableWithoutTableThrowsException()
    {
        $this->setExpectedException('\Exception');
        $obj = new DatabaseSource();
        $obj->create_table();
    }

    public function testCreateTableWithoutModelThrowsException()
    {
        $this->setExpectedException('\Exception');
        $obj = new DatabaseSource();
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

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $this->assertNotTrue($obj->table_exists());

        $ret = $obj->create_table();
        $this->assertTrue($ret);
        $this->assertTrue($obj->table_exists());
    }

    public function testCreateTableTableExistsReturnsTrue()
    {
        $obj = new DatabaseSource();
        //$obj->set_model($model);
        $obj->set_table('test');
        $this->assertTrue($obj->table_exists());

        $ret = $obj->create_table();
        $this->assertTrue($ret);
    }

    public function testCreateAlterWithoutModelThrowsException()
    {
        $this->setExpectedException('\Exception');
        
        $obj = new DatabaseSource();
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

        $obj = new DatabaseSource();
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

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->alter_table();

        $this->assertTrue($ret);
    }
}
