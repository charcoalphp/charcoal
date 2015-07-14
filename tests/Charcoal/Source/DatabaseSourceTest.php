<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Source\DatabaseSource as DatabaseSource;

use \Charcoal\Model\Object as Object;

class DatabaseSourceTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        // include 'DatabaseTestModel.php';

        Charcoal::config()->add_database(
            'unit_test',
            [
                'username' => 'root',
                'password' => '',
                'database' => 'charcoal_examples'
            ]
        );

        Charcoal::config()->set_default_database('unit_test');

        $obj = new DatabaseSource();
        // $obj->set_model($model);
        $obj->set_table('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $obj->db()->query($q);
    }

    public function testSetDatabaseIdent()
    {
        $obj = new DatabaseSource();
        $this->assertEquals('unit_test', $obj->database_ident());

        $ret = $obj->set_database_ident('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->database_ident());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_database_ident(null);
    }

    public function testSetDatabaseConfig()
    {
        $this->setExpectedException('\Exception');

        $obj = new DatabaseSource();
        $ret = $obj->database_config();
        $this->assertEquals(
            [
                'username' => 'root',
                'password' => '',
                'database' => 'charcoal_examples'
            ],
            $ret
        );

        $cfg = [
            'username' => 'x',
            'password' => 'y',
            'database' => 'z'
        ];
        $ret = $obj->set_database_config($cfg);
        $this->assertSame($ret, $obj);
        $this->assertEquals($cfg, $obj->database_config());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_database_config(false);
    }

    public function testTableWithoutSetterThrowsException()
    {
        $this->setExpectedException('\Exception');

        $obj = new DatabaseSource();
        $obj->table();
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
        $model->set_metadata(
            [
                'properties' => [
                    'id' => [
                        'type' => 'id'
                    ]
                ],
                'sources' => []
            ]
        );

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
        // $obj->set_model($model);
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
        $model->set_metadata(
            [
                'properties' => [
                    'id' => [
                        'type' => 'id'
                    ],
                    'name' => [
                        'type' => 'string',
                        'max_length' => 120
                    ]
                ]
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->alter_table();

        $this->assertTrue($ret);
    }

    public function testAlterTablePropertyChange()
    {
        $model = new Object();
        $model->set_metadata(
            [
                'properties' => [
                    'id' => [
                        'type' => 'id',

                    ],
                    'name' => [
                        'type' => 'string',
                        'max_length' => 300
                    ]
                ]
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->alter_table();

        $this->assertTrue($ret);
    }

    protected function getItemModel()
    {
        $model = new Object();
        $model->set_metadata(
            [
                'properties' => [
                    'id' => [
                        'type' => 'id',

                    ],
                    'name' => [
                        'type' => 'string',
                        'max_length' => 300
                    ],
                    '_ignore' => [
                        'type'   => 'string',
                        'active' => false
                    ]
                ]
            ]
        );
        return $model;
    }

    public function testSaveItem()
    {
        $model = $this->getItemModel();

        $model->set_data(
            [
                'id'   => 1,
                'name' => 'Foo bar'
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->save_item($model);
        $this->assertEquals(1, $ret);
    }

    public function testLoadItem()
    {
        $model = $this->getItemModel();

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->load_item(1);
        $this->assertEquals('Foo bar', $ret->name);
    }

    public function testLoadItemNoMatchingId()
    {
        $model = $this->getItemModel();

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->load_item(666);
        $this->assertNull($ret->id());
    }

    public function testUpdateItem()
    {
        $model = $this->getItemModel();

        $model->set_data(
            [
                'id'   => 1,
                'name' => 'Baz Foo'
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->update_item($model);
        $this->assertTrue($ret);

        $loaded = $obj->load_item(1);
        $this->assertEquals('Baz Foo', $loaded->name);
    }

    public function testDeleteItem()
    {
        $model = $this->getItemModel();

        $model->set_data(
            [
                'id' => 1
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->delete_item($model);
        $this->assertTrue($ret);

        $loaded = $obj->load_item(1);
        $this->assertNull($loaded->id());
    }

    public function testDeleteItemInvalidIds()
    {
        $model = $this->getItemModel();

        $model->set_data(
            [
                'id' => 42
            ]
        );

        $obj = new DatabaseSource();
        $obj->set_model($model);
        $obj->set_table('test');
        $ret = $obj->delete_item($model);
        // $this->assertFalse($ret);

        $model2 = $this->getItemModel();

        $this->setExpectedException('\Exception');
        $obj->delete_item($model2);
    }
}
