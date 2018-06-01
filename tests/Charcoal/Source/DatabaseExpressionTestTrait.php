<?php

namespace Charcoal\Tests\Source;

use DateTime;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;

/**
 *
 */
trait DatabaseExpressionTestTrait
{
    /**
     * Create expression for testing.
     *
     * @return ExpressionInterface
     */
    abstract protected function createExpression();

    /**
     * Test influence of "active" property on SQL compilation.
     *
     * @return void
     */
    abstract public function testInactiveExpression();

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see ExpressionInterface}
     *
     * @return void
     */
    public function testConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(ExpressionInterface::class, $obj);
    }
}
