<?php

namespace Charcoal\Tests\Source;

use RuntimeException;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\Service\MetadataLoader;
use Charcoal\Source\AbstractSource;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;
use Charcoal\Source\SourceConfig;

/**
 *
 */
class AbstractSourceTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForAbstractClass(AbstractSource::class, [[
            'logger' => $container['logger']
        ]]);
    }

    /**
     * Assert that the `reset` method:
     * - is chainable
     * - clear the properties
     * - clear the filters
     * - clear the orders
     * - @todo clear the pagination
     */
    public function testReset()
    {
        $obj    = $this->obj;
        $filter = $this->getFilter1();
        $order  = $this->getOrder1();
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
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'properties' => [ 'foo' ],
            'filters'    => [],
            'orders'     => [],
            'pagination' => []
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals([ 'foo' ], $obj->properties());
    }

    /**
     * Assert that the `setModel` method:
     * - is chainable
     * - set the model (retrievable with the `model` method)
     */
    public function testSetModel()
    {
        $container = $this->getContainer();

        $obj = $this->obj;
        $model = new \Charcoal\Model\Model([
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

    public function testModelWithoutSetThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException(RuntimeException::class);
        $obj->model();
    }

    /**
     * Assert that the `setProperties` method:
     * - is chainable
     * - set the properties
     * - reset the properties, when called again
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

    public function testAddProperty()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->properties());

        $ret = $obj->addProperty('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals([ 'foo' ], $obj->properties());
        $obj->addProperty('bar');
        $this->assertEquals([ 'foo', 'bar' ], $obj->properties());

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->addProperty(false);
    }

    public function testAddPropertyEmptyPropThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException(InvalidArgumentException::class);
        $obj->addProperty('');
    }

    /**
     * Assert that the `setFilters` method:
     * - is chainable
     * - set the filters member
     * - reset the filters, when called again
     */
    public function testSetFilters()
    {
        $filter1 = $this->getFilter1();
        $filter2 = $this->getFilter2();
        $obj = $this->obj;
        $ret = $obj->setFilters([ $filter1 ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([ $filter1 ], $obj->filters());

        $obj->setFilters([ $filter2 ]);
        $this->assertEquals([ $filter2 ], $obj->filters());
    }

    /**
     * Assert that the `addFilter` method:
     * - is chainable
     * - add the filter object to the filters
     * - append the filter, when a filter already exists
     * - create and add a filter object when passing an array
     * - allow passing $property, $val, $options as 3 parameters
     * - throws an exception when an invalid argument type is passed
     */
    public function testAddFilterObject()
    {
        $filter1 = $this->getFilter1();
        $filter2 = $this->getFilter2();

        $obj = $this->obj;
        $ret = $obj->addFilter($filter1);
        $this->assertSame($ret, $obj);
        $this->assertEquals([ $filter1 ], $obj->filters());

        $obj->addFilter($filter2);
        $this->assertEquals([ $filter1, $filter2 ], $obj->filters());

        $obj->addFilter([
            'property' => 'baz'
        ]);
        $obj->addFilter('foobar', '4', [ 'operator' => '<' ]);

        $filters = $obj->filters();
        $this->assertEquals(4, count($filters));
        $this->assertInstanceOf(FilterInterface::class, $filters[2]);
        $this->assertInstanceOf(FilterInterface::class, $filters[3]);

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->addFilter(true);
    }

    /**
     * Assert that the `setOrders` method:
     * - is chainable
     * - set the orders member
     * - reset the orders, when called again
     */
    public function testSetOrders()
    {
        $order1 = $this->getOrder1();
        $order2 = $this->getOrder2();
        $obj = $this->obj;
        $ret = $obj->setOrders([ $order1 ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals( [$order1 ], $obj->orders());

        $obj->setOrders([ $order2 ]);
        $this->assertEquals([ $order2 ], $obj->orders());
    }

    /**
     * Assert that the `addOrder` method:
     * - is chainable
     * - add the order object to the orders
     * - append the order, when a order already exists
     * - create and add a order object when passing an array
     * - allow passing $property, $val, $options as 3 parameters
     * - throws an exception when an invalid argument type is passed
     */
    public function testAddOrderObject()
    {
        $order1 = $this->getOrder1();
        $order2 = $this->getOrder2();

        $obj = $this->obj;
        $ret = $obj->addOrder($order1);
        $this->assertSame($ret, $obj);
        $this->assertEquals([ $order1 ], $obj->orders());

        $obj->addOrder($order2);
        $this->assertEquals([ $order1, $order2 ], $obj->orders());

        $obj->addOrder([
            'property'=>'baz'
        ]);
        $obj->addOrder('foobar', 'desc', [ 'values'=> [ 1, 2 ] ]);

        $orders = $obj->orders();
        $this->assertEquals(4, count($orders));
        $this->assertInstanceOf(OrderInterface::class, $orders[2]);
        $this->assertInstanceOf(OrderInterface::class, $orders[3]);

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->addOrder(true);
    }

    public function testSetPagination()
    {
        $p = new Pagination();
        $obj = $this->obj;

        $ret = $obj->setPagination($p);
        $this->assertSame($ret, $obj);
        $this->assertSame($p, $obj->pagination());

        $obj->setPagination([ 'page' => 3, 'num_per_page' => 120 ]);
        $this->assertInstanceOf(Pagination::class, $obj->pagination());
        $this->assertEquals(3, $obj->page());
        $this->assertEquals(120, $obj->numPerPage());

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setPagination(false);
    }

    public function testPaginationWithoutSetReturnsObject()
    {
        $obj = $this->obj;
        $p = $obj->pagination();
        $this->assertInstanceOf(Pagination::class, $p);
    }

    public function testSetPage()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->page());
        $ret = $obj->setPage(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->page());

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setPage('foo');
    }

    public function testNumPerPage()
    {
        $obj = $this->obj;
        $this->assertEquals(Pagination::DEFAULT_NUM_PER_PAGE, $obj->numPerPage());
        $ret = $obj->setNumPerPage(666);
        $this->assertSame($ret, $obj);
        $this->assertEquals(666, $obj->numPerPage());

        $this->setExpectedException(InvalidArgumentException::class);
        $obj->setNumPerPage('foobar');
    }

    public function testCreateConfig()
    {
        $obj = $this->obj;
        $config = $obj->createConfig([ 'type' => 'foo' ]);
        $this->assertInstanceOf(SourceConfig::class, $config);
        $this->assertEquals('foo', $config->type());
    }

    public function getFilter1()
    {
        $filter1 = new Filter();
        $filter1->setData([
            'property' => 'foo',
            'operator' => '=',
            'val'      => 42
        ]);
        return $filter1;
    }

    public function getFilter2()
    {
        $filter2 = new Filter();
        $filter2->setData([
            'property' => 'bar',
            'operator' => '>',
            'val'      => 666
        ]);
        return $filter2;
    }

    public function getOrder1()
    {
        $order1 = new Order();
        $order1->setData([
            'mode' => 'asc'
        ]);
        return $order1;
    }

    public function getOrder2()
    {
        $order2 = new Order();
        $order2->setData([
            'mode' => 'desc'
        ]);
        return $order2;
    }
}
