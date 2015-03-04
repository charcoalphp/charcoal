<?php

namespace Charcoal\Tests\Loader;

use \Charcoal\Loader\CollectionLoader;
use \Charcoal\Charcoal as Charcoal;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new CollectionLoader();
		$this->assertInstanceOf('\Charcoal\Loader\CollectionLoader', $obj);
	}

	/*
	public function testAll()
	{
		Charcoal::$config['databases'] = [
			'default'=>[
				'database'=>'test',
				'username'=>'root',
				'password'=>'mat123'
			]
		];
		Charcoal::$config['default_database'] = 'default';

		$source = new \Charcoal\Model\Source();
		$source->set_table('tests');

		$loader = new CollectionLoader();
		$loader->set_source($source)
			//->set_obj_type()
			->set_properties(['id', 'test'])
			->add_filter('test', 10, ['operator'=>'<'])
			->add_filter('allo', 1, ['operator'=>'>='])
			->add_order('test', 'asc')
			//->add_order(null, 'rand')
			->set_page(1)
			->set_num_per_page(10);
	
		$collection = $loader->load();
		
		$this->assertEquals(1,1);
		
	}
	*/


}