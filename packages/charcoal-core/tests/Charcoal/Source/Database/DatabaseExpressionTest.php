<?php

namespace Charcoal\Tests\Source\Database;

use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\Database\DatabaseExpression;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabaseExpression}.
 */
class DatabaseExpressionTest extends AbstractTestCase
{
    use DatabaseExpressionTestTrait;
    use ReflectionsTrait;

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
     *
     * @return void
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
     *
     * @return void
     */
    public function testCustomSql()
    {
        $obj = $this->createExpression();

        $obj->setCondition('1 = 1');
        $this->assertEquals('1 = 1', $obj->sql());
    }

    /**
     * Test invalid custom SQL.
     *
     * @return void
     */
    public function testCustomSqlWithoutQuery()
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }
}
