<?php

namespace Charcoal\Tests\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpression;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabaseExpression}.
 */
class DatabaseExpressionTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return DatabaseExpression
     */
    final protected function createExpression()
    {
        return new DatabaseExpression();
    }

    /**
     * Test influence of "active" property on SQL compilation.
     */
    public function testInactiveExpression()
    {
        $obj = $this->createExpression();
        $obj->setCondition('   /* xyzzy */   ');

        $obj->setActive(true);
        $this->assertEquals('/* xyzzy */', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test "condition" property.
     */
    public function testCustomSql()
    {
        $obj = $this->createExpression();

        $obj->setCondition('1 = 1');
        $this->assertEquals('1 = 1', $obj->sql());
    }

    /**
     * Test invalid custom SQL.
     */
    public function testCustomSqlWithoutQuery()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }
}
