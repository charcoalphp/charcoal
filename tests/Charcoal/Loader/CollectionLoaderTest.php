<?php

namespace Charcoal\Tests\Loader;

use \Charcoal\Loader\CollectionLoader as CollectionLoader;
use \Charcoal\Charcoal as Charcoal;

class CollectionLoaderTest extends \PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $factory = new \Charcoal\Model\ModelFactory();
        $this->obj = new CollectionLoader([
            'logger' => new \Psr\Log\NullLogger(),
            'factory' => $factory
        ]);
    }

    public function setData()
    {
        $obj = $this->obj;
        $obj->setData(
            [
                'properties' => [
                    'id',
                    'test'
                ]
            ]
        );
        $this->assertEquals(['id', 'test'], $obj->properties());
    }

    public function setDataIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setData([]);
        $this->assertSame($ret, $obj);
    }

    public function testAll()
    {
        /*
        $source = new \Charcoal\Model\Source();
        $source->set_table('tests');

        $loader = new CollectionLoader();
        $loader->set_source($source)
            // ->set_obj_type()
            ->set_properties(['id', 'test'])
            ->add_filter('test', 10, ['operator' => '<'])
            ->add_filter('allo', 1, ['operator' => '>='])
            ->add_order('test', 'asc')
            // ->add_order(null, 'rand')
            ->set_page(1)
            ->set_num_per_page(10);

        $collection = $loader->load();

        $this->assertEquals(1,1);
        */
        $this->assertTrue(true);
    }
}
