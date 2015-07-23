<?php

namespace Charcoal\Tests\Source\Database;

use \Charcoal\Source\Database\DatabasePagination as DatabasePagination;

class DatabasePaginationTest extends \PHPUnit_Framework_TestCase
{
    public function testSQLEmptyWithoutPage()
    {
        $obj = new DatabasePagination();
        $sql = $obj->sql();

        $this->assertEquals('', $sql);

        $obj = new DatabasePagination();
        $obj->set_num_per_page(20);
        $sql = $obj->sql();

        $this->assertEquals('', $sql);

        $obj = new DatabasePagination();
        $obj->set_page(1);
        $sql = $obj->sql();

        $this->assertEquals('', $sql);
    }

    public function testSQL()
    {
        $obj = new DatabasePagination();
        $obj->set_page(1);
        $obj->set_num_per_page(20);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 0, 20', $sql);

        $obj = new DatabasePagination();
        $obj->set_page(2);
        $obj->set_num_per_page(25);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 25, 25', $sql);

        $obj = new DatabasePagination();
        $obj->set_page(5);
        $obj->set_num_per_page(50);
        $sql = $obj->sql();

        $this->assertEquals(' LIMIT 200, 50', $sql);
    }
}
