<?php

// namespace Charcoal\Tests\Source;

// use \Charcoal\Charcoal as Charcoal;

// use \Charcoal\Source\DatabaseSource as DatabaseSource;

// class DatabaseSourceTest extends \PHPUnit_Framework_TestCase
// {
//     public static function setUpBeforeClass()
//     {
//         // include 'DatabaseTestModel.php';

//         Charcoal::config()->add_database(
//             'unit_test',
//             [
//                 'username' => 'root',
//                 'password' => '',
//                 'database' => 'charcoal_examples'
//             ]
//         );

//         Charcoal::config()->set_default_database('unit_test');

//         $obj = new DatabaseSource();
//         // $obj->set_model($item);
//         //$obj->set_table('test');
//         $q = 'DROP TABLE IF EXISTS `test`';
//         $obj->db()->query($q);

//         $q2 = 'DROP TABLE IF EXISTS `empty_test`';
//         $obj->db()->query($q2);
//     }

//     /**
//     * Assert that the method `set_database_ident()`:
//     * - is chainable
//     * - sets the database ident propertly in object
//     * - throws an exception if the parameter is not a string
//     */
//     public function testSetDatabaseIdent()
//     {
//         $obj = new DatabaseSource();
//         $this->assertEquals('unit_test', $obj->database_ident());

//         $ret = $obj->set_database_ident('foo');
//         $this->assertSame($ret, $obj);
//         $this->assertEquals('foo', $obj->database_ident());

//         $this->setExpectedException('\InvalidArgumentException');
//         $obj->set_database_ident(null);
//     }

//     public function testSetDatabaseConfig()
//     {
//         $this->setExpectedException('\Exception');

//         $obj = new DatabaseSource();
//         $ret = $obj->database_config();
//         $this->assertEquals(
//             [
//                 'username' => 'root',
//                 'password' => '',
//                 'database' => 'charcoal_examples'
//             ],
//             $ret
//         );

//         $cfg = [
//             'username' => 'x',
//             'password' => 'y',
//             'database' => 'z'
//         ];
//         $ret = $obj->set_database_config($cfg);
//         $this->assertSame($ret, $obj);
//         $this->assertEquals($cfg, $obj->database_config());

//         $this->setExpectedException('\InvalidArgumentException');
//         $obj->set_database_config(false);
//     }

//     /**
//     * Assert that calling the `table()` method without first having set a table throws an exception.
//     */
//     public function testTableWithoutSetterThrowsException()
//     {
//         $this->setExpectedException('\Exception');

//         $obj = new DatabaseSource();
//         $obj->table();
//     }

//     /**
//     * Assert that, with the method `set_table()`:
//     * - setting the table change the table.
//     * - the method is chainable.
//     * - passing a non-string argument throws an exception.
//     */
//     public function testSetTable()
//     {
//         $obj = new DatabaseSource();
//         $ret = $obj->set_table('foo');
//         $this->assertSame($ret, $obj);
//         $this->assertEquals('foo', $obj->table());

//         $this->setExpectedException('\InvalidArgumentException');
//         $obj->set_table(null);
//     }

//     public function testCreateTableWithoutTableThrowsException()
//     {
//         $this->setExpectedException('\Exception');
//         $obj = new DatabaseSource();
//         $obj->create_table();
//     }

//     public function testCreateTableWithoutModelThrowsException()
//     {
//         $this->setExpectedException('\Exception');
//         $obj = new DatabaseSource();
//         $obj->set_table('foo');
//         $obj->create_table();
//     }

//     // public function testCreateTable()
//     // {
//     //     $item = new Object();
//     //     $item->set_metadata(
//     //         [
//     //             'properties' => [
//     //                 'id' => [
//     //                     'type' => 'id'
//     //                 ]
//     //             ],
//     //             'sources' => []
//     //         ]
//     //     );

//     //     $obj = new DatabaseSource();
//     //     $obj->set_model($item);
//     //     $obj->set_table('test');
//     //     $this->assertNotTrue($obj->table_exists());

//     //     $ret = $obj->create_table();
//     //     $this->assertTrue($ret);
//     //     $this->assertTrue($obj->table_exists());
//     // }

//     // public function testCreateTableTableExistsReturnsTrue()
//     // {
//     //     $obj = new DatabaseSource();
//     //     // $obj->set_model($item);
//     //     $obj->set_table('test');
//     //     $this->assertTrue($obj->table_exists());

//     //     $ret = $obj->create_table();
//     //     $this->assertTrue($ret);
//     // }

//     // public function testCreateAlterWithoutModelThrowsException()
//     // {
//     //     $this->setExpectedException('\Exception');

//     //     $obj = new DatabaseSource();
//     //     $obj->set_table('test');

//     //     $this->assertTrue($obj->table_exists());
//     //     $obj->alter_table();
//     // }

//     // public function testAlterTableNewProperty()
//     // {
//     //     $item = new Object();
//     //     $item->set_metadata(
//     //         [
//     //             'properties' => [
//     //                 'id' => [
//     //                     'type' => 'id'
//     //                 ],
//     //                 'name' => [
//     //                     'type' => 'string',
//     //                     'max_length' => 120
//     //                 ]
//     //             ]
//     //         ]
//     //     );

//     //     $obj = new DatabaseSource();
//     //     $obj->set_model($item);
//     //     $obj->set_table('test');
//     //     $ret = $obj->alter_table();

//     //     $this->assertTrue($ret);
//     // }

//     // public function testAlterTablePropertyChange()
//     // {
//     //     $item = new Object();
//     //     $item->set_metadata(
//     //         [
//     //             'properties' => [
//     //                 'id' => [
//     //                     'type' => 'id',

//     //                 ],
//     //                 'name' => [
//     //                     'type' => 'string',
//     //                     'max_length' => 300
//     //                 ]
//     //             ]
//     //         ]
//     //     );

//     //     $obj = new DatabaseSource();
//     //     $obj->set_model($item);
//     //     $obj->set_table('test');
//     //     $ret = $obj->alter_table();

//     //     $this->assertTrue($ret);
//     // }

//     // public function testAlterTableInvalidTableReturnFalse()
//     // {
//     //     $obj = new DatabaseSource();
//     //     $obj->set_table('invalid-table');
//     //     $this->assertFalse($obj->alter_table());
//     // }


//     // public function testTableExists()
//     // {
//     //     $obj = new DatabaseSource();
//     //     $obj->set_table('invalid-table');
//     //     $this->assertFalse($obj->table_exists());
//     //     $obj->set_table('test');
//     //     $this->assertTrue($obj->table_exists());
//     // }

//     // public function testTableStructure()
//     // {
//     //     $obj = new DatabaseSource();
//     //     $obj->set_table('test');
//     //     $ret = $obj->table_structure();
//     //     $this->assertNotEmpty($ret);
//     // }

//     // /**
//     // * Assert that, with the method `table_is_empty()`:
//     // * - using with an empty table returns true.
//     // * - using with a non-empty table returns false.
//     // * - using with an invalid table throws a PDOException
//     // */
//     // public function testTableIsEmpty()
//     // {
//     //     $obj = new DatabaseSource();
//     //     $obj->set_table('empty_test');

//     //     $item = $this->getItemModel();
//     //     $obj->set_model($item);
//     //     $obj->create_table();
//     //     $this->assertTrue($obj->table_is_empty());

//     //     $item->set_data(['id'=>1, 'name'=>'Empty Test']);
//     //     $obj->save_item($item);
//     //     $this->assertFalse($obj->table_is_empty());

//     //     $this->setExpectedException('\PDOException');
//     //     $obj->set_table('invalid-db-table');
//     //     $obj->table_is_empty();
//     // }


//     // protected function getItemModel()
//     // {
//     //     $item = new Object();
//     //     $item->set_metadata(
//     //         [
//     //             'properties' => [
//     //                 'id' => [
//     //                     'type' => 'id',

//     //                 ],
//     //                 'name' => [
//     //                     'type' => 'string',
//     //                     'max_length' => 300
//     //                 ],
//     //                 '_ignore' => [
//     //                     'type'   => 'string',
//     //                     'active' => false
//     //                 ]
//     //             ]
//     //         ]
//     //     );
//     //     return $item;
//     // }

//     public function testSaveItem()
//     {
//         $item = $this->getItemModel();

//         $item->set_data(
//             [
//                 'id'   => 1,
//                 'name' => 'Foo bar'
//             ]
//         );

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->save_item($item);
//         $this->assertEquals(1, $ret);
//     }

//     /**
//     * @depends testSaveItem
//     */
//     public function testLoadItem()
//     {
//         $item = $this->getItemModel();

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->load_item(1);
//         $this->assertInstanceOf(get_class($item), $ret);
//         $this->assertEquals('Foo bar', $ret->name);
//     }

//     public function testLoadItemNoMatchingId()
//     {
//         $item = $this->getItemModel();

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->load_item(666);
//         $this->assertNull($ret->id());
//     }

//     public function testLoadItems()
//     {
//         $item = $this->getItemModel();

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');

//         $ret = $obj->load_items();
//         //var_dump($ret);
//     }

//     public function testUpdateItem()
//     {
//         $item = $this->getItemModel();

//         $item->set_data(
//             [
//                 'id'   => 1,
//                 'name' => 'Baz Foo'
//             ]
//         );

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->update_item($item);
//         $this->assertTrue($ret);

//         $loaded = $obj->load_item(1);
//         $this->assertEquals('Baz Foo', $loaded->name);
//     }

//     public function testDeleteItem()
//     {
//         $item = $this->getItemModel();

//         $item->set_data(
//             [
//                 'id' => 1
//             ]
//         );

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->delete_item($item);
//         $this->assertTrue($ret);

//         $loaded = $obj->load_item(1);
//         $this->assertNull($loaded->id());
//     }

//     public function testDeleteItemInvalidIds()
//     {
//         $item = $this->getItemModel();

//         $item->set_data(
//             [
//                 'id' => 42
//             ]
//         );

//         $obj = new DatabaseSource();
//         $obj->set_model($item);
//         $obj->set_table('test');
//         $ret = $obj->delete_item($item);
//         // $this->assertFalse($ret);

//         $item2 = $this->getItemModel();

//         $this->setExpectedException('\Exception');
//         $obj->delete_item($item2);
//     }
// }
