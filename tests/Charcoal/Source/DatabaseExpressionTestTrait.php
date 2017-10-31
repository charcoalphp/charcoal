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
     * Retrieve access to a non-public method.
     *
     * @param  object $obj  The object to access.
     * @param  string $name The method name to access.
     * @return ReflectionMethod
     */
    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see ExpressionInterface}
     */
    public function testContruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(ExpressionInterface::class, $obj);
    }

    /**
     * Test "active" property.
     */
    public function testInactiveExpression()
    {
        $obj = $this->createExpression();
        $obj->setData([ 'condition' => ' /* xyzzy */ ' ]);

        $obj->setActive(true);
        $this->assertEquals('/* xyzzy */', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
    }
}
