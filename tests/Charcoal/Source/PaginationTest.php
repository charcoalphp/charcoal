<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Pagination;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\Source\QueryExpressionTestTrait;

/**
 *
 */
class PaginationTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use QueryExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return Pagination
     */
    final protected function createExpression()
    {
        return new Pagination();
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by QueryExpressionTestTrait::testDefaultValues()
     * @return  array
     */
    final public function provideDefaultValues()
    {
        return [
            'page num'  => [ 'page',         1 ],
            'per page'  => [ 'num_per_page', 0 ],
            'active'    => [ 'active',       true ],
            'condition' => [ 'condition',    null ],
        ];
    }

    /**
     * Test the "page" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts float
     * 5. Swaps zero for one
     */
    public function testPageNum()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(1, $obj->page());

        /** 2. Mutated Value */
        $that = $obj->setPage(3);
        $this->assertInternalType('integer', $obj->page());
        $this->assertEquals(3, $obj->page());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts float */
        $obj->setPage(5.5);
        $this->assertEquals(5, $obj->page());

        /** 5. Swaps zero for one */
        $obj->setPage(0);
        $this->assertEquals(1, $obj->page());
    }

    /**
     * Test "page" property with negative value.
     */
    public function testPageNumWithNegativeValue()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setPage(-5);
    }

    /**
     * Test "page" property with invalid value.
     */
    public function testPageNumWithInvalidValue()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setPage(null);
    }

    /**
     * Test the "num_per_page" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts float
     */
    public function testNumPerPage()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(0, $obj->numPerPage());

        /** 2. Mutated Value */
        $that = $obj->setNumPerPage(10);
        $this->assertInternalType('integer', $obj->numPerPage());
        $this->assertEquals(10, $obj->numPerPage());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts float */
        $obj->setNumPerPage(10.3);
        $this->assertEquals(10, $obj->numPerPage());
    }

    /**
     * Test "num_per_page" property with negative value.
     */
    public function testNumPerPageWithNegativeValue()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setNumPerPage(-5);
    }

    /**
     * Test "num_per_page" property with invalid value.
     */
    public function testNumPerPageWithInvalidValue()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setNumPerPage(null);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     */
    public function testData()
    {
        $obj = $this->createExpression();

        /** 1. Mutate all options */
        $mutation = [
            'page'         => 3,
            'num_per_page' => 25,
            'active'       => false,
            'condition'          => '1 = 1',
        ];

        $obj->setData($mutation);
        $data = $obj->data();

        $this->assertArrayHasKey('page', $data);
        $this->assertEquals(3, $data['page']);
        $this->assertEquals(3, $obj->page());

        $this->assertArrayHasKey('num_per_page', $data);
        $this->assertEquals(25, $data['num_per_page']);
        $this->assertEquals(25, $obj->numPerPage());

        $this->assertStructHasBasicData($obj, $mutation);

        /** 2. Partially mutated state */
        $mutation = [
            'num_per_page' => 10
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $this->assertEquals(1, $obj->page());
        $this->assertTrue($obj->active());
        $this->assertNull($obj->condition());

        $data = $obj->data();
        $this->assertArrayNotHasKey('page', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('condition', $data);

        $this->assertArrayHasKey('num_per_page', $data);
        $this->assertEquals(10, $data['num_per_page']);
    }

    /**
     * Test lowest possible index.
     */
    public function testFirst()
    {
        $obj = $this->createExpression();

        $obj->setPage(1);
        $obj->setNumPerPage(0);
        $this->assertEquals(0, $obj->first());

        $obj->setPage(3);
        $obj->setNumPerPage(20);
        $this->assertEquals(40, $obj->first());
    }

    /**
     * Test highest possible index.
     */
    public function testLast()
    {
        $obj = $this->createExpression();

        $obj->setPage(1);
        $obj->setNumPerPage(0);
        $this->assertEquals(0, $obj->last());

        $obj->setPage(3);
        $obj->setNumPerPage(20);
        $this->assertEquals(60, $obj->last());
    }
}
