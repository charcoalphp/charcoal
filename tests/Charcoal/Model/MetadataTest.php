<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Metadata;

class MetadataTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new Metadata();
		$this->assertInstanceOf('\Charcoal\Model\Metadata', $obj);
	}

	public function testArrayAccessGet()
	{
		$obj = new Metadata();
		$obj->foo = 'bar';

		$this->assertEquals($obj->foo, $obj['foo']);
	}

	public function testArrayAccessSet()
	{
		$obj = new Metadata();
		$obj['foo'] = 'bar';

		$this->assertEquals($obj->foo, $obj['foo']);
	}

	public function testArrayAccessSetWithNoOffsetThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Metadata();
		$obj[] = 'bar';
	}

	public function testArrayAccessUnset()
	{
		$obj = new Metadata();
		$this->assertObjectNotHasAttribute('foo', $obj);

		$obj['foo'] = 'bar';
		$this->assertObjectHasAttribute('foo', $obj);

		unset($obj['foo']);
		$this->assertObjectNotHasAttribute('foo', $obj);

	}

	public function testSetDataSetsData()
	{
		$data = [
			'foo' => 'bar',
			'bar' => 'foo'
		];

		$obj = new Metadata();
		$obj->set_data($data);

		$this->assertEquals($obj->foo, 'bar');
		$this->assertEquals($obj->bar, 'foo');
	}

	public function testSetDataIsChainable()
	{
		$obj = new Metadata();
		$ret = $obj->set_data([]);

		$this->assertSame($obj, $ret);
	}

	/**
	* @dataProvider invalidDataProvider
	*/
	public function testSetDataInvalidParameterThrowException($invalid_data)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Metadata();
		$obj->set_data($invalid_data);
	}



	public function invalidDataProvider()
	{
		$obj = new \StdClass();
		return [
			['string'],
			[123],
			[null],
			[$obj]
		];
	}
}
