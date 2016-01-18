<?php

namespace Charcoal\Tests\Source;

class AbstractSourceTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Source\AbstractSource');
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
        $obj = $this->obj;
        $filter = $this->getFilter1();
        $order = $this->getOrder1();
        $pagination = ['page'=>3, 'num_per_page'=>120];
        $obj->setData([
            'properties'=>['foo'],
            'filters'=>[$filter],
            'orders'=>[$order],
            'pagination'=>$pagination
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
            'properties'=>['foo'],
            'filters'=>[],
            'orders'=>[],
            'pagination'=>[]
        ]);
        $this->assertSame($ret, $obj);

        $this->assertEquals(['foo'], $obj->properties());
    }

    /**
    * Assert that the `set_model` method:
    * - is chainable
    * - set the model (retrievable with the `model` method)
    */
    public function testSetModel()
    {
        $obj = $this->obj;
        $model = new \Charcoal\Model\Model();
        $ret = $obj->set_model($model);
        $this->assertSame($ret, $obj);
        $this->assertSame($model, $obj->model());
    }

    public function testModelWithoutSetThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\Exception');
        $obj->model();
    }

    /**
    * Assert that the `set_properties` method:
    * - is chainable
    * - set the properties
    * - reset the properties, when called again
    */
    public function testSetProperties()
    {
        $obj = $this->obj;
        $ret = $obj->set_properties(['foo']);
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'], $obj->properties());

        $obj->set_properties(['bar']);
        $this->assertEquals(['bar'], $obj->properties());
    }

    public function testAddProperty()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->properties());

        $ret = $obj->add_property('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['foo'], $obj->properties());
        $obj->add_property('bar');
        $this->assertEquals(['foo', 'bar'], $obj->properties());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_property(false);
    }

    public function testAddPropertyEmptyPropThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_property('');
    }

    /**
    * Assert that the `set_filters` method:
    * - is chainable
    * - set the filters member
    * - reset the filters, when called again
    */
    public function testSetFilters()
    {
        $filter1 = $this->getFilter1();
        $filter2 = $this->getFilter2();
        $obj = $this->obj;
        $ret = $obj->set_filters([$filter1]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([$filter1], $obj->filters());

        $obj->set_filters([$filter2]);
        $this->assertEquals([$filter2], $obj->filters());
    }

    /**
    * Assert that the `add_filter` method:
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
        $ret = $obj->add_filter($filter1);
        $this->assertSame($ret, $obj);
        $this->assertEquals([$filter1], $obj->filters());

        $obj->add_filter($filter2);
        $this->assertEquals([$filter1, $filter2], $obj->filters());

        $obj->add_filter([
            'property'=>'baz'
        ]);
        $obj->add_filter('foobar', '4', ['operator'=>'<']);

        $filters = $obj->filters();
        $this->assertEquals(4, count($filters));
        $this->assertInstanceOf('\Charcoal\Source\Filter', $filters[2]);
        $this->assertInstanceOf('\Charcoal\Source\Filter', $filters[3]);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_filter(true);
    }

    /**
    * Assert that the `set_orders` method:
    * - is chainable
    * - set the orders member
    * - reset the orders, when called again
    */
    public function testSetOrders()
    {
        $order1 = $this->getOrder1();
        $order2 = $this->getOrder2();
        $obj = $this->obj;
        $ret = $obj->set_orders([$order1]);
        $this->assertSame($ret, $obj);
        $this->assertEquals([$order1], $obj->orders());

        $obj->set_orders([$order2]);
        $this->assertEquals([$order2], $obj->orders());
    }

    /**
    * Assert that the `add_order` method:
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
        $ret = $obj->add_order($order1);
        $this->assertSame($ret, $obj);
        $this->assertEquals([$order1], $obj->orders());

        $obj->add_order($order2);
        $this->assertEquals([$order1, $order2], $obj->orders());

        $obj->add_order([
            'property'=>'baz'
        ]);
        $obj->add_order('foobar', 'desc', ['values'=>[1, 2]]);

        $orders = $obj->orders();
        $this->assertEquals(4, count($orders));
        $this->assertInstanceOf('\Charcoal\Source\Order', $orders[2]);
        $this->assertInstanceOf('\Charcoal\Source\Order', $orders[3]);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->add_order(true);
    }

    public function testSetPagination()
    {
        $p = new \Charcoal\Source\Pagination();
        $obj = $this->obj;

        $ret = $obj->set_pagination($p);
        $this->assertSame($ret, $obj);
        $this->assertSame($p, $obj->pagination());

        $obj->set_pagination(['page'=>3, 'num_per_page'=>120]);
        $this->assertInstanceOf('\Charcoal\Source\Pagination', $obj->pagination());
        $this->assertEquals(3, $obj->page());
        $this->assertEquals(120, $obj->num_per_page());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_pagination(false);
    }

    public function testPaginationWithoutSetReturnsObject()
    {
        $obj = $this->obj;
        $p = $obj->pagination();
        $this->assertInstanceOf('\Charcoal\Source\Pagination', $p);
    }

    public function testSetPage()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->page());
        $ret = $obj->set_page(42);
        $this->assertSame($ret, $obj);
        $this->assertEquals(42, $obj->page());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_page('foo');
    }

    public function testNumPerPage()
    {
        $obj = $this->obj;
        $this->assertEquals(\Charcoal\Source\Pagination::DEFAULT_NUM_PER_PAGE, $obj->num_per_page());
        $ret = $obj->set_num_per_page(666);
        $this->assertSame($ret, $obj);
        $this->assertEquals(666, $obj->num_per_page());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->set_num_per_page('foobar');
    }

    public function testCreateConfig()
    {
        $obj = $this->obj;
        $config = $obj->create_config(['type'=>'foo']);
        $this->assertInstanceOf('\Charcoal\Source\SourceConfig', $config);
        $this->assertEquals('foo', $config->type());
    }

    public function getFilter1()
    {
        $filter1 = new \Charcoal\Source\Filter();
        $filter1->setData([
            'property'=>'foo',
            'operator'=>'=',
            'val'=>42
        ]);
        return $filter1;
    }

    public function getFilter2()
    {
        $filter2 = new \Charcoal\Source\Filter();
        $filter2->setData([
            'property'=>'bar',
            'operator'=>'>',
            'val'=>666
        ]);
        return $filter2;
    }

    public function getOrder1()
    {
        $order1 = new \Charcoal\Source\Order();
        $order1->setData([
            'mode'=>'asc'
        ]);
        return $order1;
    }

    public function getOrder2()
    {
        $order2 = new \Charcoal\Source\Order();
        $order2->setData([
            'mode'=>'desc'
        ]);
        return $order2;
    }
}
