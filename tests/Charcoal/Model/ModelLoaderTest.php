<?php

namespace Charcoal\Tests\Metadata;

use \Charcoal\Model\ModelLoader;
use \Charcoal\Charcoal;

class ModelLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $metadataLoader = new \Charcoal\Model\MetadataLoader([
            'logger' => new \Psr\Log\NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'cache'  => new \Stash\Pool()
        ]);

        $factory = new \Charcoal\Model\ModelFactory;
        $factory->setArguments([
            'logger'=> new \Psr\Log\NullLogger(),
            'metadata_loader' => $metadataLoader
        ]);

        $this->obj = new ModelLoader([
            'obj_type' => 'charcoal/model/model',
            'factory' => $factory,
            'logger' => new \Psr\Log\NullLogger(),
            'cache'  => new \Stash\Pool()
        ]);
    }

    public function testArrayAccess()
    {
        $obj = $this->obj;
        $this->assertTrue(true);
        //$data = $this->obj['test'];
        //$this->assertEquals('string', $data['properties']['test']['type']);
    }
}
