<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Pagination} and {@see PaginationInterface}.
 */
class PaginationTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use ExpressionTestTrait;

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
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see PaginationInterface}
     *
     * @return void
     */
    public function testPaginationConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(PaginationInterface::class, $obj);
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by ExpressionTestTrait::testDefaultValues()
     * @return  array
     */
    final public function provideDefaultValues()
    {
        return [
            'page num' => [ 'page',         1 ],
            'per page' => [ 'num_per_page', 0 ],
            'active'   => [ 'active',       true ],
            'name'     => [ 'name',         null ],
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
     *
     * @return void
     */
    public function testPageNum()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(1, $obj->page());

        /** 2. Mutated Value */
        $that = $obj->setPage(3);
        $this->assertIsInt($obj->page());
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
     *
     * @return void
     */
    public function testPageNumWithNegativeValue()
    {
        $obj = $this->createExpression();

        $this->expectException(InvalidArgumentException::class);
        $obj->setPage(-5);
    }

    /**
     * Test "page" property with invalid value.
     *
     * @return void
     */
    public function testPageNumWithInvalidValue()
    {
        $obj = $this->createExpression();

        $this->expectException(InvalidArgumentException::class);
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
     *
     * @return void
     */
    public function testNumPerPage()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(0, $obj->numPerPage());

        /** 2. Mutated Value */
        $that = $obj->setNumPerPage(10);
        $this->assertIsInt($obj->numPerPage());
        $this->assertEquals(10, $obj->numPerPage());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts float */
        $obj->setNumPerPage(10.3);
        $this->assertEquals(10, $obj->numPerPage());
    }

    /**
     * Test "num_per_page" property with negative value.
     *
     * @return void
     */
    public function testNumPerPageWithNegativeValue()
    {
        $obj = $this->createExpression();

        $this->expectException(InvalidArgumentException::class);
        $obj->setNumPerPage(-5);
    }

    /**
     * Test "num_per_page" property with invalid value.
     *
     * @return void
     */
    public function testNumPerPageWithInvalidValue()
    {
        $obj = $this->createExpression();

        $this->expectException(InvalidArgumentException::class);
        $obj->setNumPerPage(null);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     * 3. Mutation via aliases
     *
     * @return void
     */
    public function testData()
    {
        /** 1. Mutate all options */
        $mutation = [
            'page'         => 3,
            'num_per_page' => 25,
            'active'       => false,
            'name'         => 'foo',
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);

        $data = $obj->data();

        $this->assertArrayHasKey('page', $data);
        $this->assertEquals(3, $data['page']);
        $this->assertEquals(3, $obj->page());

        $this->assertArrayHasKey('num_per_page', $data);
        $this->assertEquals(25, $data['num_per_page']);
        $this->assertEquals(25, $obj->numPerPage());

        /** 2. Partially mutated state */
        $mutation = [
            'num_per_page' => 10
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $defs = $obj->defaultData();
        $this->assertStructHasBasicData($obj, $defs);
        $this->assertEquals($defs['page'], $obj->page());

        $data = $obj->data();
        $this->assertNotEquals($defs['num_per_page'], $data['num_per_page']);
        $this->assertEquals(10, $data['num_per_page']);

        /** 3. Mutation via aliases */
        $mutation = [
            'per_page' => 15
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $data = $obj->data();
        $this->assertEquals(15, $data['num_per_page']);
    }

    /**
     * Test lowest possible index.
     *
     * @return void
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
     *
     * @return void
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
