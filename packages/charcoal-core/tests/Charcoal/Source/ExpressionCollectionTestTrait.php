<?php

namespace Charcoal\Tests\Source;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\OrderCollectionInterface;

/**
 * Shared tests and utilities
 * for {@see FilterCollectionInterface}
 * and {@see OrderCollectionInterface}.
 */
trait ExpressionCollectionTestTrait
{
    /**
     * A throwaway array used for testing initial values.
     *
     * @var array
     */
    protected $dummyItems = [ 'foo', 'bar', 'qux' ];

    /**
     * Create expression for testing.
     *
     * @return object
     */
    abstract protected function createCollector();

    /**
     * Create expression for testing.
     *
     * @param  array $data Optional expression data.
     * @return ExpressionInterface
     */
    abstract protected function createExpression(array $data = null);

    /**
     * Test expression creation from collector.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of relevant collection
     *
     * @return void
     */
    abstract public function testCreateExpression();

    /**
     * Test collection retrieval.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @return void
     */
    abstract public function testGetExpressions();

    /**
     * Test collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @return void
     */
    abstract public function testHasExpressions();

    /**
     * Test the mass assignment of expressions.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     *
     * @return void
     */
    abstract public function testSetExpressions();

    /**
     * Test the mass addition of expressions.
     *
     * Assertions:
     * 1. Appends an array of items to the internal collection
     * 2. Chainable method
     *
     * @return void
     */
    abstract public function testAddExpressions();

    /**
     * Test the addition of one expression.
     *
     * Assertions:
     * 1. Appends one item to the internal collection
     * 2. Chainable method
     *
     * @return void
     */
    abstract public function testAddExpression();

    /**
     * Test traversal of internal collection.
     *
     * @return void
     */
    abstract public function testTraverseExpressions();
}
