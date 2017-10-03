<?php

namespace Charcoal\Tests\Source\Database;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabasePagination;

/**
 *
 */
class DatabasePaginationTest extends \PHPUnit_Framework_TestCase
{
    public function testSQLEmptyWithoutPage()
    {
        $obj = new DatabasePagination();
        $sql = $obj->sql();

        $this->assertEquals('', $sql);

        $obj = new DatabasePagination();
        $obj->setNumPerPage(20);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 0, 20', $sql);

        $obj = new DatabasePagination();
        $obj->setPage(1);
        $sql = $obj->sql();

        $this->assertEquals('', $sql);
    }

    public function testSQL()
    {
        $obj = new DatabasePagination();
        $obj->setPage(1);
        $obj->setNumPerPage(20);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 0, 20', $sql);

        $obj = new DatabasePagination();
        $obj->setPage(2);
        $obj->setNumPerPage(25);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 25, 25', $sql);

        $obj = new DatabasePagination();
        $obj->setPage(5);
        $obj->setNumPerPage(50);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 200, 50', $sql);
    }
}
