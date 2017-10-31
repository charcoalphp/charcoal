<?php

namespace Charcoal\Tests\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabasePagination;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabasePagination}.
 */
class DatabasePaginationTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return DatabasePagination
     */
    final protected function createExpression()
    {
        return new DatabasePagination();
    }

    /**
     * Test influence of "active" property on SQL compilation.
     */
    public function testInactiveExpression()
    {
        $obj = $this->createExpression();
        $obj->setNumPerPage(10);

        $obj->setActive(true);
        $this->assertEquals('LIMIT 0, 10', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test "page" property without "num_per_page".
     */
    public function testSqlOffsetWithoutLimit()
    {
        $obj = $this->createExpression();

        $obj->setPage(1);
        $this->assertEquals('', $obj->sql());

        $obj->setPage(5);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test "page" property with "num_per_page".
     */
    public function testSqlOffsetWithLimit()
    {
        $obj = $this->createExpression();

        $obj->setNumPerPage(12);
        $this->assertEquals('LIMIT 0, 12', $obj->sql());

        $obj->setPage(2);
        $this->assertEquals('LIMIT 12, 12', $obj->sql());

        $obj->setPage(5);
        $this->assertEquals('LIMIT 48, 12', $obj->sql());
    }

    /**
     * Test "num_per_page" property without "page".
     */
    public function testSqlLimitWithoutOffset()
    {
        $obj = $this->createExpression();

        $obj->setNumPerPage(1);
        $this->assertEquals('LIMIT 0, 1', $obj->sql());

        $obj->setNumPerPage(12);
        $this->assertEquals('LIMIT 0, 12', $obj->sql());
    }

    /**
     * Test helper methods.
     */
    public function testUtilities()
    {
        $obj = $this->createExpression();

        $obj->setPage(3);
        $obj->setNumPerPage(12);

        $this->assertEquals(12, $obj->limit());
        $this->assertEquals(24, $obj->offset());

        $obj->setNumPerPage(PHP_INT_MAX);
        $this->assertEquals(PHP_INT_MAX, $obj->offset());
    }
}
