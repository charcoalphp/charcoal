<?php

namespace Charcoal\Tests\Metadata;

use \Charcoal\Model\MetadataLoader;
use \Charcoal\Charcoal;

class MetadataLoaderTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new MetadataLoader([
            'logger' => new \Psr\Log\NullLogger(),
            'base_path' => __DIR__,
            'paths' => ['metadata'],
            'config' => $GLOBALS['container']['config'],
            'cache'  => $GLOBALS['container']['cache']
        ]);
    }

    public function testLoadData()
    {
        $data = $this->obj->loadData('test');
        $this->assertEquals('string', $data['properties']['test']['type']);
    }
}
