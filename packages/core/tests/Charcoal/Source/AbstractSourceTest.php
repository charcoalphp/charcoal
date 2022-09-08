<?php

namespace Charcoal\Tests\Source;

use RuntimeException;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-core'
use Charcoal\Model\Model;
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\AbstractSource;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionInterface;
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;
use Charcoal\Source\SourceConfig;
use Charcoal\Source\SourceInterface;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Mock\OrderTree;
use Charcoal\Tests\ReflectionsTrait;

/**
 * Test {@see AbstractSource} and {@see SourceInterface}.
 */
class AbstractSourceTest extends AbstractTestCase
{
    use AssertionsTrait;
    use CoreContainerIntegrationTrait;
    use ReflectionsTrait;

    /**
     * The tested class.
     *
     * @var AbstractSource
     */
    public $obj;

    /**
     * Setup the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForAbstractClass(AbstractSource::class, [[
            'logger' => $container['logger']
        ]]);
    }

    /**
     * Create mock property for testing.
     *
     * @return PropertyInterface
     */
    final public function createProperty()
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create('generic');
        $prop->setIdent('xyzzy');

        return $prop;
    }

    /**
     * Assert that the `reset` method:
     * - is chainable
     * - clear the properties
     * - clear the filters
     * - clear the orders
     * - @todo clear the pagination
     *
     * @return void
     */
    public function testReset()
    {
        $obj    = $this->obj;
        $filter = $this->createFilter();
        $order  = $this->createOrder();
        $paged  = [
            'page' => 3,
            'num_per_page' => 120
        ];
        $obj->setData([
            'properties' => [ 'foo' ],
            'filters'    => [ $filter ],
            'orders'     => [ $order ],
            'pagination' => $paged
        ]);
        $ret = $obj->reset();
        $this->assertSame($ret, $obj);

        $this->assertEquals([], $obj->properties());
        $this->assertEquals([], $obj->filters());
        $this->assertEquals([], $obj->orders());
        //$this->assertEquals(null, $obj->pagination());
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - set the data (properties, filters, orders & pagination)
     *
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'properties' => [ 'foo' ],
            'filters'    => [],
            'orders'     => [],
            'pagination' => [],
            'foobar'     => true
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals([ 'foo' ], $obj->properties());
        $this->assertTrue($obj->foobar);
    }

    /**
     * Assert that the `setModel` method:
     * - is chainable
     * - set the model (retrievable with the `model` method)
     *
     * @return void
     */
    public function testSetModel()
    {
        $container = $this->getContainer();

        $obj = $this->obj;
        $model = new Model([
            'logger'          => $container['logger'],
            'metadata_loader' => new MetadataLoader([
                'base_path'   => '',
                'paths'       => [],
                'logger'      => $container['logger'],
                'cache'       => $container['cache']
            ])
        ]);
        $ret = $obj->setModel($model);
        $this->assertSame($ret, $obj);
        $this->assertSame($model, $obj->model());
    }

    /**
     * @return void
     */
    public function testModelWithoutSetThrowsException()
    {
        $obj = $this->obj;
        $this->expectException(RuntimeException::class);
        $obj->model();
    }

    /**
     * Assert that the `setProperties` method:
     * - is chainable
     * - set the properties
     * - reset the properties, when called again
     *
     * @covers \Charcoal\Source\AbstractSource::setProperties
     * @covers \Charcoal\Source\AbstractSource::addProperties
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testSetProperties()
    {
        $obj = $this->obj;
        $ret = $obj->setProperties([ 'foo' ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([ 'foo' ], $obj->properties());

        $obj->setProperties([ 'bar' ]);
        $this->assertEquals([ 'bar' ], $obj->properties());
    }

    /**
     * Test property collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\AbstractSource::hasProperties
     *
     * @return void
     */
    public function testHasProperties()
    {
        $obj = $this->obj;

        /** 1. Default state */
        $this->assertFalse($obj->hasProperties());

        /** 2. Mutated state */
        $obj->setProperties([ 'foo' ]);
        $this->assertTrue($obj->hasProperties());
    }

    /**
     * Test property collection appending.
     *
     * @covers \Charcoal\Source\AbstractSource::addProperty
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testAddProperty()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->properties());

        $ret = $obj->addProperty('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals([ 'foo' ], $obj->properties());
        $obj->addProperty('bar');
        $this->assertEquals([ 'foo', 'bar' ], $obj->properties());
    }

    /**
     * Test property collection appending.
     *
     * @covers \Charcoal\Source\AbstractSource::removeProperty
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testRemoveProperty()
    {
        $obj = $this->obj;
        $obj->setProperties([ 'foo', 'bar', 'qux' ]);

        $ret = $obj->removeProperty('foo');
        $this->assertSame($ret, $obj);
        $this->assertNotContains('foo', $obj->properties());
    }

    /**
     * Test failure when appending an invalid property name.
     *
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testInvalidPropertyNameResolution()
    {
        $obj = $this->obj;
        $this->expectException(InvalidArgumentException::class);
        $obj->addProperty(false);
    }

    /**
     * Test failure when appending an blank property name.
     *
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testBlankPropertyNameResolution()
    {
        $obj = $this->obj;
        $this->expectException(InvalidArgumentException::class);
        $obj->addProperty('');
    }

    /**
     * Test failure when appending a unnamed property object.
     *
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testAnonymousPropertyNameResolution()
    {
        $obj  = $this->obj;
        $prop = $this->createProperty()->setIdent('');
        $this->expectException(InvalidArgumentException::class);
        $obj->addProperty($prop);
    }

    /**
     * Test appending a named property object.
     *
     * @covers \Charcoal\Source\AbstractSource::resolvePropertyName
     *
     * @return void
     */
    public function testNamedPropertyNameResolution()
    {
        $obj  = $this->obj;
        $prop = $this->createProperty();

        $obj->addProperty($prop);
        $this->assertContains('xyzzy', $obj->properties());
    }

    /**
     * Test the addition of one query filter expression (customized method).
     *
     * Assertions:
     * 1. If a string is provided as the first argument,
     *    an Expression object with a condition is returned
     * 2. If a string is provided as the first argument
     *    and a non-NULL value is provided as the second argument,
     *    an Comparison object is returned
     * 3. If an instance of {@see FilterInterface} is provided,
     *    the Expression object is used as is.
     * 4. If an array is provided with an options subset,
     *    an Expression object with given data is returned
     * 5. If a third argument is provided,
     *    an Expression object with given extra data is returned
     * 6. Chainable method
     *
     * @covers \Charcoal\Source\AbstractSource::addFilter
     *
     * @return void
     */
    public function testAddFilter()
    {
        $obj = $this->obj;

        /** 1. Condition */
        $condition = '`foo` = "Charcoal"';
        $obj->reset()->addFilter($condition);
        $result = $obj->filters();
        $result = end($result);
        $this->assertEquals($condition, $result->condition());

        /** 2. Comparison */
        $field = 'foo';
        $value = 'Charcoal';
        $obj->reset()->addFilter($field, $value);
        $result = $obj->filters();
        $result = end($result);
        $this->assertEquals($field, $result->property());
        $this->assertEquals($value, $result->value());

        /** 3. Expression */
        $expr = $this->createFilter([ 'name' => 'foo' ]);
        $obj->reset()->addFilter($expr);
        $result = $obj->filters();
        $result = end($result);
        $this->assertSame($expr, $result);

        /** 4. Structure with options subset */
        $struct = [
            'name'     => 'foo',
            'options'  => [
                'name' => 'bar'
            ]
        ];
        $obj->reset()->addFilter($struct);
        $result = $obj->filters();
        $result = end($result);
        $this->assertEquals('bar', $result->name());

        /** 5. Expression with extra options */
        $expr   = $this->createFilter([ 'name' => 'foo' ]);
        $that   = $obj->reset()->addFilter($expr, null, [ 'name' => 'bar' ]);
        $result = $obj->filters();
        $result = end($result);
        $this->assertEquals('bar', $result->name());

        /** 6. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the post-processing of a query Filter expression.
     *
     * Assertions:
     * 1. If a multilingual property is defined for the expression,
     *    the source will set the field name for the current locale.
     * 2. If a multi-value property is defined for the expression,
     *    the source will set the comparison operator for a valueset.
     * 3. If a tree of expressions is passed, the source will traverse
     *    all expressions.
     *
     * @covers \Charcoal\Source\AbstractSource::parseFilterWithModel
     *
     * @return void
     */
    public function testParseFilterWithModel()
    {
        $model  = $this->createModel();
        $source = $this->obj;
        $source->setModel($model);
        $method = $this->getMethod($source, 'parseFilterWithModel');

        /** 1. Use current locale for multilingual properties */
        $exp1 = $this->createFilter([ 'property' => 'title', 'operator' => '=' ]);
        $this->assertEquals('title', $exp1->property());

        $result = $method->invoke($source, $exp1);
        $this->assertSame($exp1, $result);
        $this->assertEquals('title_en', $exp1->property());

        /** 2. Force operator for multi-value properties */
        $exp2 = $this->createFilter([ 'property' => 'roles', 'operator' => '=' ]);
        $this->assertEquals('=', $exp2->operator());

        $result = $method->invoke($source, $exp2);
        $this->assertSame($exp2, $result);
        $this->assertEquals('FIND_IN_SET', $exp2->operator());

        /** 3. Traversal of nested expressions */
        $exp3 = $this->createFilter();
        $exp4 = $this->createFilter([ 'filters' => [ $exp3 ] ]);

        $result = $method->invoke($source, $exp4);
        $this->assertSame($exp4, $result);
        $this->assertContains($exp3, $result->filters());
    }

    /**
     * Test the creation of a query filter expression.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see Filter}
     *
     * @see    \Charcoal\Tests\Source\FilterTest::testCreateFilter
     * @covers \Charcoal\Source\AbstractSource::createFilter
     *
     * @return void
     */
    public function testCreateFilter()
    {
        $result = $this->callMethodWith($this->obj, 'createFilter', [ 'name' => 'foo' ]);
        $this->assertInstanceOf(Filter::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
        $this->assertEquals('foo', $result->name());
    }

    /**
     * Test the addition of one query order expression (customized method).
     *
     * Assertions:
     * 1. If a string is provided as the first argument,
     *    an Expression object with a condition is returned
     * 2. If a string is provided as the first argument
     *    and a non-NULL value is provided as the second argument,
     *    an Expression object sorting by field is returned
     * 3. If an instance of {@see OrderInterface} is provided,
     *    the Expression object is used as is.
     * 4. If an array is provided with an options subset,
     *    an Expression object with given data is returned
     * 5. If a third argument is provided,
     *    an Expression object with given extra data is returned
     * 6. Chainable method
     *
     * @covers \Charcoal\Source\AbstractSource::addOrder
     *
     * @return void
     */
    public function testAddOrder()
    {
        $obj = $this->obj;

        /** 1. Condition */
        $condition = '`foo` ASC';
        $obj->reset()->addOrder($condition, null);
        $result = $obj->orders();
        $result = end($result);
        $this->assertEquals($condition, $result->condition());

        /** 2. Sort by field */
        $field = 'foo';
        $mode  = 'desc';
        $obj->reset()->addOrder($field, $mode);
        $result = $obj->orders();
        $result = end($result);
        $this->assertEquals($field, $result->property());
        $this->assertEquals($mode, $result->mode());

        /** 3. Expression */
        $expr = $this->createOrder([ 'name' => 'foo' ]);
        $obj->reset()->addOrder($expr);
        $result = $obj->orders();
        $result = end($result);
        $this->assertSame($expr, $result);

        /** 4. Structure with options subset */
        $struct = [
            'name'     => 'foo',
            'options'  => [
                'name' => 'bar'
            ]
        ];
        $obj->reset()->addOrder($struct);
        $result = $obj->orders();
        $result = end($result);
        $this->assertEquals('bar', $result->name());

        /** 5. Expression with extra options */
        $expr   = $this->createOrder([ 'name' => 'foo' ]);
        $that   = $obj->reset()->addOrder($expr, null, [ 'name' => 'bar' ]);
        $result = $obj->orders();
        $result = end($result);
        $this->assertEquals('bar', $result->name());

        /** 6. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the post-processing of a query Order expression.
     *
     * Assertions:
     * 1. If a multilingual property is defined for the expression,
     *    the source will set the field name for the current locale.
     * 2. If a tree of expressions is passed, the source will traverse
     *    all expressions.
     *
     * @covers \Charcoal\Source\AbstractSource::parseOrderWithModel
     *
     * @return void
     */
    public function testParseOrderWithModel()
    {
        $model  = $this->createModel();
        $source = $this->obj;
        $source->setModel($model);
        $method = $this->getMethod($source, 'parseOrderWithModel');

        /** 1. Use current locale for multilingual properties */
        $exp1 = $this->createOrder([ 'property' => 'title', 'direction' => 'ASC' ]);
        $this->assertEquals('title', $exp1->property());

        $result = $method->invoke($source, $exp1);
        $this->assertSame($exp1, $result);
        $this->assertEquals('title_en', $exp1->property());

        /** 2. Traversal of nested expressions */
        $exp2 = new OrderTree();
        $exp3 = new OrderTree();

        $exp2->addOrder($exp3);

        $result = $method->invoke($source, $exp2);
        $this->assertSame($exp2, $result);
        $this->assertContains($exp3, $result->orders());
    }

    /**
     * Test the creation of a query sorting expression.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see Order}
     *
     * @covers \Charcoal\Source\AbstractSource::createOrder
     *
     * @return void
     */
    public function testCreateOrder()
    {
        $result = $this->callMethodWith($this->obj, 'createOrder', [ 'name' => 'foo' ]);
        $this->assertInstanceOf(Order::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
        $this->assertEquals('foo', $result->name());
    }

    /**
     * Test pagination returns instance of {@see PaginationInterface}.
     *
     * Assertions:
     * 1. Default state is NULL
     * 2. Create paginator if state is NULL
     *
     * @covers \Charcoal\Source\AbstractSource::pagination
     * @covers \Charcoal\Source\AbstractSource::hasPagination
     *
     * @return void
     */
    public function testGetPagination()
    {
        /** 1. Default state is NULL */
        $this->assertFalse($this->obj->hasPagination());

        /** 2. Create paginator if state is NULL */
        $result = $this->obj->pagination();
        $this->assertTrue($this->obj->hasPagination());
        $this->assertInstanceOf(Pagination::class, $result);
    }

    /**
     * Test pagination assignment.
     *
     * Assertions:
     * 1. Accepts instance of {@see PaginationInterface}
     * 2. Replaces expression with a new instance
     * 3. Accepts an array structure
     * 4. Accepts up to two numeric arguments
     * 5. Chainable method
     *
     * @covers \Charcoal\Source\AbstractSource::setPagination
     *
     * @return void
     */
    public function testSetPagination()
    {
        $obj = $this->obj;

        /** 1. Accepts instance of {@see PaginationInterface}. */
        $exp1 = $this->createPagination([ 'name' => 'foo' ]);
        $that = $obj->setPagination($exp1);
        $this->assertSame($exp1, $obj->pagination());

        /** 2. Replaces expression with a new instance. */
        $exp2 = $this->createPagination([ 'name' => 'bar' ]);
        $obj->setPagination($exp2);
        $this->assertSame($exp2, $obj->pagination());

        /** 3. Accepts an array structure */
        $struct = [ 'page' => 3, 'num_per_page' => 10 ];
        $obj->setPagination($struct);
        $exp3 = $obj->pagination();
        $this->assertInstanceOf(PaginationInterface::class, $exp3);
        $this->assertArrayContains($struct, $exp3->data());
        $this->assertEquals(3, $obj->page());
        $this->assertEquals(10, $obj->numPerPage());

        /** 4. Accepts up to two numeric arguments */
        $obj->setPagination(5, 20);
        $exp4 = $obj->pagination();
        $this->assertInstanceOf(PaginationInterface::class, $exp4);
        $this->assertEquals(5, $exp4->page());
        $this->assertEquals(20, $exp4->numPerPage());

        /** 5. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the failure when assigning an invalid pagination expression.
     *
     * @covers \Charcoal\Source\AbstractSource::setPagination
     *
     * @return void
     */
    public function testProcessExpressionWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setPagination(false);
    }

    /**
     * Test the creation of a query pagination expression.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see PaginationInterface}
     *
     * @covers \Charcoal\Source\AbstractSource::createPagination
     *
     * @return void
     */
    public function testCreatePagination()
    {
        $result = $this->callMethodWith($this->obj, 'createPagination', [ 'name' => 'foo' ]);
        $this->assertInstanceOf(Pagination::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
        $this->assertEquals('foo', $result->name());
    }

    /**
     * @return void
     */
    public function testSetPage()
    {
        $obj = $this->obj;
        $this->assertEquals(1, $obj->page());

        $ret = $obj->setPage(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->page());

        $this->expectException(InvalidArgumentException::class);
        $obj->setPage('foo');
    }

    /**
     * @return void
     */
    public function testNumPerPage()
    {
        $obj = $this->obj;
        $this->assertEquals(Pagination::DEFAULT_COUNT, $obj->numPerPage());
        $ret = $obj->setNumPerPage(666);
        $this->assertSame($ret, $obj);
        $this->assertEquals(666, $obj->numPerPage());

        $this->expectException(InvalidArgumentException::class);
        $obj->setNumPerPage('foobar');
    }

    /**
     * @return void
     */
    public function testCreateConfig()
    {
        $obj = $this->obj;
        $config = $obj->createConfig([ 'type' => 'foo' ]);
        $this->assertInstanceOf(SourceConfig::class, $config);
        $this->assertEquals('foo', $config->type());
    }

    /**
     * Test camelization.
     *
     * @covers \Charcoal\Source\AbstractSource::camelize
     * @covers \Charcoal\Source\AbstractSource::getter
     * @covers \Charcoal\Source\AbstractSource::setter
     *
     * @return void
     */
    public function testCamelize()
    {
        $obj = $this->obj;

        $getter = $this->getMethod($obj, 'getter');
        $setter = $this->getMethod($obj, 'setter');

        $this->assertEquals('charcoalPhp', $getter->invoke($obj, 'charcoal_php'));
        $this->assertEquals('setCharcoalPhp', $setter->invoke($obj, 'charcoal_php'));
    }

    /**
     * Create a query filter expression, for testing.
     *
     * @param  array $data Optional expression data.
     * @return Filter
     */
    final public function createFilter(array $data = null)
    {
        $expr = new Filter();
        if ($data !== null) {
            $expr->setData($data);
        }
        return $expr;
    }

    /**
     * Create query sorting expression, for testing.
     *
     * @param  array $data Optional expression data.
     * @return Order
     */
    final public function createOrder(array $data = null)
    {
        $expr = new Order();
        if ($data !== null) {
            $expr->setData($data);
        }
        return $expr;
    }

    /**
     * Create query pagination expression, for testing.
     *
     * @param  array $data Optional expression data.
     * @return Pagination
     */
    final public function createPagination(array $data = null)
    {
        $expr = new Pagination();
        if ($data !== null) {
            $expr->setData($data);
        }
        return $expr;
    }

    /**
     * Create a new model instance.
     *
     * @return Model
     */
    final protected function createModel()
    {
        $container = $this->getContainer();

        $obj = $container['model/factory']->create(Model::class);
        $obj->setMetadata($this->getModelMetadata());

        return $obj;
    }

    /**
     * Retrieve the model's mock metadata.
     *
     * @return array
     */
    final protected function getModelMetadata()
    {
        return [
            'properties' => [
                'id' => [
                    'type' => 'id'
                ],
                'name' => [
                    'type' => 'string'
                ],
                'title' => [
                    'type' => 'string',
                    'l10n' => true
                ],
                'roles' => [
                    'type'     => 'string',
                    'multiple' => true
                ]
            ],
            'key' => 'id'
        ];
    }
}
