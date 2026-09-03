<?php

namespace Charcoal\Tests\Source;

use Exception;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\DatabaseSourceInterface;
use Charcoal\Source\SourceInterface;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class DatabaseSourceTest extends AbstractTestCase
{
    use \Charcoal\Tests\CoreContainerIntegrationTrait;

    /**
     * @return void
     */
    public function testClass()
    {
        $container = $this->getContainer();

        $src = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database'],
        ]);

        $this->assertInstanceOf(DatabaseSourceInterface::class, $src);
        $this->assertInstanceOf(SourceInterface::class, $src);
    }

    /**
     * @return void
     */
    /*
    public static function setUpBeforeClass()
    {
        // include 'DatabaseTestModel.php';

        $obj = new DatabaseSource();
        // $obj->setModel($item);
        //$obj->setTable('test');
        $q = 'DROP TABLE IF EXISTS `test`';
        $obj->db()->query($q);

        $q2 = 'DROP TABLE IF EXISTS `empty_test`';
        $obj->db()->query($q2);
    }
    */

    /**
     * Assert that the method `setDatabaseIdent()`:
     * - is chainable
     * - sets the database ident propertly in object
     * - throws an exception if the parameter is not a string
     *
     * @return void
     */
    /*
    public function testSetDatabaseIdent()
    {
        $obj = new DatabaseSource();
        $this->assertEquals('unit_test', $obj->databaseIdent());

        $ret = $obj->setDatabaseIdent('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->databaseIdent());

        $this->expectException(InvalidArgumentException::class);
        $obj->setDatabaseIdent(null);
    }
    */

    /**
     * @return void
     */
    /*
    public function testSetDatabaseConfig()
    {
        $this->expectException(Exception::class);

        $obj = new DatabaseSource();
        $ret = $obj->databaseConfig();
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
        $ret = $obj->setDatabaseConfig($cfg);
        $this->assertSame($ret, $obj);
        $this->assertEquals($cfg, $obj->databaseConfig());

        $this->expectException(InvalidArgumentException::class);
        $obj->setDatabaseConfig(false);
    }
    */

    /**
     * Assert that calling the `table()` method without first having set a table throws an exception.
     *
     * @return void
     */
    /*
    public function testTableWithoutSetterThrowsException()
    {
        $this->expectException(Exception::class);

        $obj = new DatabaseSource();
        $obj->table();
    }
    */

    /**
     * Assert that, with the method `setTable()`:
     * - setting the table changes the table
     * - the method is chainable
     * - non-string and unsafe identifier strings throw
     *
     * @return void
     */
    public function testSetTable()
    {
        $container = $this->getContainer();

        $obj = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database'],
        ]);

        $ret = $obj->setTable('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->table());

        $obj->setTable('_leading_underscore');
        $this->assertEquals('_leading_underscore', $obj->table());

        $obj->setTable('CamelCase_Table9');
        $this->assertEquals('CamelCase_Table9', $obj->table());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTable(null);
    }

    /**
     * Injection-shaped and otherwise unsafe table names must be rejected (LS03).
     *
     * @dataProvider provideInvalidTableNames
     *
     * @param  mixed $table Invalid table name.
     * @return void
     */
    public function testSetTableRejectsUnsafeNames($table)
    {
        $container = $this->getContainer();

        $obj = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database'],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $obj->setTable($table);
    }

    /**
     * @return array<string,array{0:mixed}>
     */
    public function provideInvalidTableNames()
    {
        return [
            'empty'              => [ '' ],
            'injection_drop'     => [ 'users; DROP TABLE secrets--' ],
            'injection_comment'  => [ 'foo--' ],
            'space'              => [ 'foo bar' ],
            'hyphen'             => [ 'foo-bar' ],
            'dot_schema'         => [ 'schema.table' ],
            'backtick'           => [ 'foo`bar' ],
            'leading_digit'      => [ '1table' ],
            'semicolon'          => [ 'a;b' ],
            'quote_breakout'     => [ "foo' OR '1'='1" ],
        ];
    }

    /**
     * Assert that, with the method `setTable()`:
     * - setting the table change the table.
     * - the method is chainable.
     * - passing a non-string argument throws an exception.
     *
     * @return void
     */
    /*
    public function testSetTableLegacy()
    {
        $obj = new DatabaseSource();
        $ret = $obj->setTable('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->table());

        $this->expectException(InvalidArgumentException::class);
        $obj->setTable(null);
    }
    */

    /**
     * @return void
     */
    /*
    public function testCreateTableWithoutTableThrowsException()
    {
        $this->expectException(Exception::class);
        $obj = new DatabaseSource();
        $obj->createTable();
    }
    */

    /**
     * @return void
     */
    /*
    public function testCreateTableWithoutModelThrowsException()
    {
        $this->expectException(Exception::class);
        $obj = new DatabaseSource();
        $obj->setTable('foo');
        $obj->createTable();
    }
    */

    /**
     * @return void
     */
    /*
    public function testCreateTable()
    {
        $item = new Object();
        $item->setMetadata(
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
        $obj->setModel($item);
        $obj->setTable('test');
        $this->assertNotTrue($obj->tableExists());

        $ret = $obj->createTable();
        $this->assertTrue($ret);
        $this->assertTrue($obj->tableExists());
    }
    */

    /**
     * @return void
     */
    /*
    public function testCreateTableTableExistsReturnsTrue()
    {
        $obj = new DatabaseSource();
        // $obj->setModel($item);
        $obj->setTable('test');
        $this->assertTrue($obj->tableExists());

        $ret = $obj->createTable();
        $this->assertTrue($ret);
    }
    */

    /**
     * @return void
     */
    /*
    public function testCreateAlterWithoutModelThrowsException()
    {
        $this->expectException(Exception::class);

        $obj = new DatabaseSource();
        $obj->setTable('test');

        $this->assertTrue($obj->tableExists());
        $obj->alterTable();
    }
    */

    /**
     * @return void
     */
    /*
    public function testAlterTableNewProperty()
    {
        $item = new Object();
        $item->setMetadata(
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
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->alterTable();

        $this->assertTrue($ret);
    }
    */

    /**
     * @return void
     */
    /*
    public function testAlterTablePropertyChange()
    {
        $item = new Object();
        $item->setMetadata(
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
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->alterTable();

        $this->assertTrue($ret);
    }
    */

    /**
     * @return void
     */
    /*
    public function testAlterTableInvalidTableReturnFalse()
    {
        $obj = new DatabaseSource();
        $obj->setTable('invalid-table');
        $this->assertFalse($obj->alterTable());
    }
    */

    /**
     * @return void
     */
    /*
    public function testTableExists()
    {
        $obj = new DatabaseSource();
        $obj->setTable('invalid-table');
        $this->assertFalse($obj->tableExists());
        $obj->setTable('test');
        $this->assertTrue($obj->tableExists());
    }
    */

    /**
     * @return void
     */
    /*
    public function testTableStructure()
    {
        $obj = new DatabaseSource();
        $obj->setTable('test');
        $ret = $obj->tableStructure();
        $this->assertNotEmpty($ret);
    }
    */

    /**
     * Assert that, with the method `tableIsEmpty()`:
     * - using with an empty table returns true.
     * - using with a non-empty table returns false.
     * - using with an invalid table throws a PDOException
     *
     * @return void
     */
    /*
    public function testTableIsEmpty()
    {
        $obj = new DatabaseSource();
        $obj->setTable('empty_test');

        $item = $this->getItemModel();
        $obj->setModel($item);
        $obj->createTable();
        $this->assertTrue($obj->tableIsEmpty());

        $item->setData(['id' => 1, 'name' => 'Empty Test']);
        $obj->saveItem($item);
        $this->assertFalse($obj->tableIsEmpty());

        $this->expectException(PDOException::class);
        $obj->setTable('invalid-db-table');
        $obj->tableIsEmpty();
    }
    */

    /**
     * @return void
     */
    /*
    protected function getItemModel()
    {
        $item = new Object();
        $item->setMetadata([
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
        ]);
        return $item;
    }
    */

    /**
     * @return void
     */
    /*
    public function testSaveItem()
    {
        $item = $this->getItemModel();

        $item->setData(
            [
                'id'   => 1,
                'name' => 'Foo bar'
            ]
        );

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->saveItem($item);
        $this->assertEquals(1, $ret);
    }
    */

    /**
     * @depends testSaveItem
     * @return void
     */
    /*
    public function testLoadItem()
    {
        $item = $this->getItemModel();

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->loadItem(1);
        $this->assertInstanceOf(get_class($item), $ret);
        $this->assertEquals('Foo bar', $ret->name);
    }
    */

    /**
     * @return void
     */
    /*
    public function testLoadItemNoMatchingId()
    {
        $item = $this->getItemModel();

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->loadItem(666);
        $this->assertNull($ret->id());
    }
    */

    /**
     * @return void
     */
    /*
    public function testLoadItems()
    {
        $item = $this->getItemModel();

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');

        $ret = $obj->loadItems();
        //var_dump($ret);
    }
    */

    /**
     * @return void
     */
    /*
    public function testUpdateItem()
    {
        $item = $this->getItemModel();

        $item->setData(
            [
                'id'   => 1,
                'name' => 'Baz Foo'
            ]
        );

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->updateItem($item);
        $this->assertTrue($ret);

        $loaded = $obj->loadItem(1);
        $this->assertEquals('Baz Foo', $loaded->name);
    }
    */

    /**
     * @return void
     */
    /*
    public function testDeleteItem()
    {
        $item = $this->getItemModel();

        $item->setData(
            [
                'id' => 1
            ]
        );

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->deleteItem($item);
        $this->assertTrue($ret);

        $loaded = $obj->loadItem(1);
        $this->assertNull($loaded->id());
    }
    */

    /**
     * @return void
     */
    /*
    public function testDeleteItemInvalidIds()
    {
        $item = $this->getItemModel();

        $item->setData(
            [
                'id' => 42
            ]
        );

        $obj = new DatabaseSource();
        $obj->setModel($item);
        $obj->setTable('test');
        $ret = $obj->deleteItem($item);
        // $this->assertFalse($ret);

        $item2 = $this->getItemModel();

        $this->expectException(Exception::class);
        $obj->deleteItem($item2);
    }
    */

    /**
     * Predicate filters expose PDO binds via filterBinds() after sqlFilters().
     *
     * @return void
     */
    public function testSqlFiltersCollectsBinds()
    {
        $container = $this->getContainer();

        $src = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database'],
        ]);

        $this->assertSame([], $src->filterBinds());

        $src->addFilter([
            'property' => 'name',
            'operator' => '=',
            'value'    => "' OR '1'='1",
        ]);

        $sql = $src->sqlFilters();
        $binds = $src->filterBinds();

        $this->assertStringStartsWith(' WHERE ', $sql);
        $this->assertMatchesRegularExpression('/:filter_\d+/', $sql);
        $this->assertStringNotContainsString("' OR '1'='1", $sql);
        $this->assertCount(1, $binds);
        $this->assertSame("' OR '1'='1", array_values($binds)[0]);

        $src->reset();
        $this->assertSame('', $src->sqlFilters());
        $this->assertSame([], $src->filterBinds());
    }

    /**
     * FIELD() orders expose PDO binds via orderBinds() / queryBinds() after sqlOrders().
     *
     * @return void
     */
    public function testSqlOrdersCollectsBinds()
    {
        $container = $this->getContainer();

        $src = new DatabaseSource([
            'logger' => $container['logger'],
            'pdo'    => $container['database'],
        ]);

        $this->assertSame([], $src->orderBinds());
        $this->assertSame([], $src->queryBinds());

        $src->addFilter([
            'property' => 'name',
            'operator' => '=',
            'value'    => 'safe',
        ]);
        $src->addOrder([
            'property' => 'country',
            'mode'     => 'values',
            'values'   => [ "' OR '1'='1", 'CA' ],
        ]);

        $filterSql = $src->sqlFilters();
        $orderSql  = $src->sqlOrders();
        $binds     = $src->queryBinds();

        $this->assertStringStartsWith(' WHERE ', $filterSql);
        $this->assertStringStartsWith(' ORDER BY ', $orderSql);
        $this->assertMatchesRegularExpression('/:filter_\d+/', $filterSql);
        $this->assertMatchesRegularExpression('/FIELD\(`objTable`\.`country`, :order_\d+, :order_\d+\)/', $orderSql);
        $this->assertStringNotContainsString("' OR '1'='1", $orderSql);
        $this->assertGreaterThanOrEqual(3, count($binds));
        $this->assertContains("' OR '1'='1", $binds);
        $this->assertContains('CA', $binds);
        $this->assertContains('safe', $binds);
    }
}
