<?php

namespace Charcoal\Tests\Source\Database;

use \Charcoal\Source\Database\DatabaseFilter as DatabaseFilter;

class DatabaseFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testSQLNoPropertyIsEmpty()
    {
        $obj = new DatabaseFilter();
        $sql = $obj->sql();

        $this->assertEquals('', $sql);
    }

    /**
    * @dataProvider providerBasicOperators
    */
    public function testSQLBasicOperators($operator)
    {
        $obj = new DatabaseFilter();
        $obj->set_property('foo');
        $obj->set_operator($operator);
        $obj->set_val('bar');
        $sql = $obj->sql();

        /** @todo: Note that 'bar' is not quoted... */
        $this->assertEquals('(`foo` '.$operator.' \'bar\')', $sql);
    }

    /**
    * @dataProvider providerNullStyleOperators
    */
    public function testSQLNullStyleOperators($operator)
    {
        $obj = new DatabaseFilter();
        $obj->set_property('foo');
        $obj->set_operator($operator);
        $obj->set_val('bar');
        $sql = $obj->sql();

        /** @todo: Note that 'bar' is not quoted... */
        $this->assertEquals('(`foo` '.$operator.')', $sql);
    }

    public function testSQLFunction()
    {
        $obj = new DatabaseFilter();
        $obj->set_property('foo');
        $obj->set_operator('=');
        $obj->set_val('bar');
        $obj->set_func('abs');
        $sql = $obj->sql();

        /** @todo: Note that 'bar' is not quoted... */
        $this->assertEquals('(ABS(`foo`) = \'bar\')', $sql);
    }

    public function testSQLWithString()
    {
        $obj = new DatabaseFilter();
        $obj->set_string('1=1');

        $sql = $obj->sql();
        $this->assertEquals('1=1', $sql);
    }

    public function testSQLWithStringTakesPrecedence()
    {
        $obj = new DatabaseFilter();

        // Should be ignored:
        $obj->set_property('foo');
        $obj->set_operator('=');
        $obj->set_val('bar');

        // Should take precedence:
        $obj->set_string('1=1');

        $sql = $obj->sql();
        $this->assertEquals('1=1', $sql);
    }

    public function providerBasicOperators()
    {
        return [
            ['='],
            ['>'],
            ['>='],
            ['<'],
            ['>'],
            ['IS'],
            ['IS NOT'],
            ['LIKE']
        ];
    }

    public function providerNullStyleOperators()
    {
        return [
            ['IS NULL'],
            ['IS NOT NULL']
        ];
    }
}
