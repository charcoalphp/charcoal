<?php

namespace Charcoal\Tests\Loader;

use \Charcoal\Loader\MetadataLoader;
use \Charcoal\Charcoal as Charcoal;

class MetadataLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new MetadataLoader();
		$this->assertInstanceOf('\Charcoal\Loader\MetadataLoader', $obj);

		$this->assertEquals([], $obj->search_path());
		$this->assertEquals('', $obj->ident());
	}

	public function testSetIdent()
	{
		$obj = new MetadataLoader();
		$obj->set_ident('foo');
		$this->assertEquals('foo', $obj->ident());
	}

	public function testSetIdentIsChainable()
	{
		$obj = new MetadataLoader();
		$ret = $obj->set_ident('foo');
		$this->assertSame($ret, $obj);
	}

	public function testSetIdentInvalidParameterThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new MetadataLoader();
		$obj->set_ident(null);
	}

	public function testAddPath()
	{
		$obj = new MetadataLoader();
		$obj->add_path(__DIR__);

		$this->assertEquals([__DIR__], $obj->search_path());
	}

	public function testAddPathInvalidArgumentThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new MetadataLoader();
		$obj->add_path(null);
	}

	public function testAddPathInvalidPathThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new MetadataLoader();
		$obj->add_path('invalid_path');
	}

	public function testAddPathInvalidDirectoryThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new MetadataLoader();
		$obj->add_path(__DIR__.'/MetadataLoaderTest.php');
	}

	public function testSearchPathUsesGlobalConfig()
	{
		Charcoal::$config['metadata_path'] = ['/tmp'];

		$obj = new MetadataLoader();
		$this->assertEquals(['/tmp'], $obj->search_path());

		$obj->add_path(__DIR__);
		$this->assertEquals(['/tmp', __DIR__], $obj->search_path());
	}

	public function testLoadWithEmptySearchPathReturnsEmptyArray()
	{
		Charcoal::$config['metadata_path'] = [];

		$obj = new MetadataLoader();
		//$obj->add_path('metadata');
		//$obj->set_ident('test');
		//var_dump($obj->search_path());
		$ret = $obj->load();

		$this->assertEquals([], $ret);
	}

	public function testLoadWithIdentSetsIdent()
	{
		$obj = new MetadataLoader();

		$ret = $obj->load('test');

		$this->assertEquals('test', $obj->ident());
	}
}
