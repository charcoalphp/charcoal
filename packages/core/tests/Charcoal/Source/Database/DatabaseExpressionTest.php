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
     */
    final protected function createExpression(): \Charcoal\Source\Database\DatabaseExpression
    {
        return new DatabaseExpression();
    }

    /**
     * Test influence of "active" property on SQL compilation.
     */
    public function testInactiveExpression(): void
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
    public function testCustomSql(): void
    {
        $obj = $this->createExpression();

        $obj->setCondition('1 = 1');
        $this->assertEquals('1 = 1', $obj->sql());
    }

    /**
     * Test invalid custom SQL.
     */
    public function testCustomSqlWithoutQuery(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }
}
