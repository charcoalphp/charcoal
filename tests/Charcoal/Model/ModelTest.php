<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\Model;
use \Charcoal\Model\Metadata;

class ModelTest extends \PHPUnit_Framework_TestCase
{
	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new Model();
		$this->assertInstanceOf('\Charcoal\Model\Model', $obj);
	}

	public function testSetMetadataFromArray()
	{
		$data = [
			'data'=>[
				'foo'=>'bar',
				'bar'=>'foo'
			]
		];

		$obj = new Model();
		$obj->set_metadata($data);
		
		$metadata = $obj->metadata();
		$this->assertSame($metadata['data'], $data['data']);
	}

	public function testSetMetadataSetsData()
	{
		$data = [
			'data'=>[
				'foo'=>'bar',
				'bar'=>'foo'
			]
		];

		$obj = new Model();
		$obj->set_metadata($data);
		
		$this->assertEquals($obj->foo, 'bar');
		$this->assertEquals($obj->bar, 'foo');
	}

	public function testSetMetadataIsChainable()
	{
		$obj = new Model();
		$ret = $obj->set_metadata([]);

		$this->assertSame($obj, $ret);
	}

	/**
	* @dataProvider invalidMetadataProvider
	*/
	public function testSetDataInvalidParameterThrowException($invalid_data)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Model();
		$obj->set_metadata($invalid_data);
	}

	public function invalidMetadataProvider()
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

