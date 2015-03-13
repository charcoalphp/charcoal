<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Collection as Collection;
use \Charcoal\Model\Object as Object;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	public $obj;
	public function setUp()
	{
		$this->obj = new Object();
		$this->obj->set_id('foo');

		$this->obj2 = new Object();
		$this->obj2->set_id('bar');
	}
	public function testContructor()
	{
		$collection =  new Collection();
		$this->assertInstanceOf('\Charcoal\Model\Collection', $collection);
	}

	public function testArrayAccessSet()
	{
		$collection =  new Collection();
		$collection[] = $this->obj;

		$this->assertEquals($this->obj, $collection['foo']);
		$this->assertSame($this->obj, $collection[0]);
	}

	public function testArrayAccessSetWithOffsetThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$collection =  new Collection();
		$collection['bar'] = $this->obj;
	}

	public function testArrayAccessSetWithInvalidValueThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$collection =  new Collection();
		$collection[] = 'foo';
	}

	public function testArrayAccessGet()
	{
		$collection =  new Collection();
		$collection->add($this->obj);

		$this->assertSame($this->obj, $collection['foo']);
		$this->assertSame($this->obj, $collection[0]);
	}

	public function testArrayAccessGetWithInvalidOffsetThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$collection =  new Collection();
		$ret = $collection[null];
	}

	public function testArrayAccessExist()
	{
		$collection =  new Collection();

		$this->assertNotTrue(isset($collection[0]));

		$collection[] = $this->obj;

		$this->assertTrue(isset($collection[0]));
	}

	public function testArrayAccessExistByKey()
	{
		$collection =  new Collection();

		$this->assertNotTrue(isset($collection['foo']));

		$collection[] = $this->obj;

		$this->assertTrue(isset($collection['foo']));
	}

	public function testArrayAccessInvalidParameterThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$collection =  new Collection();
		unset($collection[null]);
	}

	public function testArrayAccessUnset()
	{
		$collection =  new Collection();
		$collection[] = $this->obj;

		$this->assertArrayHasKey('foo', $collection->map());
		$this->assertEquals(1, count($collection->objects()));

		unset($collection[0]);

		$this->assertArrayNotHasKey('foo', $collection->map());
		$this->assertEquals(0, count($collection->objects()));

	}

	public function testArrayAccessUnsetByKey()
	{
		$collection =  new Collection();
		$collection[] = $this->obj;

		$this->assertArrayHasKey('foo', $collection->map());
		$this->assertEquals(1, count($collection->objects()));

		unset($collection['foo']);

		$this->assertArrayNotHasKey('foo', $collection->map());
		$this->assertEquals(0, count($collection->objects()));
	}

	public function testIterator()
	{
		$collection =  new Collection();
		$collection[] = $this->obj;
		$collection[] = $this->obj2;

		$tests = ['foo', 'bar'];
		$i = 0;
		foreach($collection as $id => $obj) {
			$this->assertEquals($tests[$i], $id);
			$this->assertTrue($obj instanceof Object);
			$i++;
		}
		$this->assertEquals(2, $i);
	}

	public function testIteratorEmptyCollection()
	{
		$collection =  new Collection();

		$i = 0;
		foreach($collection as $id => $obj) {
			$i++;
		}
		$this->assertEquals(0, $i);
	}

	public function testCount()
	{
		$collection = new Collection();

		$this->assertEquals(0, count($collection));

		$collection[] = $this->obj;

		$this->assertEquals(1, count($collection));

		unset($collection['foo']);

		$this->assertEquals(0, count($collection));
	}

	public function testAdd()
	{
		$collection = new Collection();
		$collection->add($this->obj);

		$this->assertSame($this->obj, $collection['foo']);
		$this->assertEquals(1, count($collection));

		$collection->add($this->obj2);

		$this->assertSame($this->obj2, $collection['bar']);
		$this->assertEquals(2, count($collection));
	}

	// todo: testRemove

	public function testPosById()
	{
		$collection = new Collection();
		$collection[] = $this->obj;
		$collection[] = $this->obj2;

		$this->assertEquals(0, $collection->pos('foo'));
		$this->assertEquals(1, $collection->pos('bar'));
	}

	public function testPosByObject()
	{
		$collection = new Collection();
		$collection[] = $this->obj;
		$collection[] = $this->obj2;

		$this->assertEquals(0, $collection->pos($this->obj));
		$this->assertEquals(1, $collection->pos($this->obj2));
	}

	public function testPosInvalidParameterThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$collection =  new Collection();
		$collection->pos(null);
	}


}