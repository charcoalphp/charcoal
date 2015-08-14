<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Source\SourceFactory;

class SourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultData()
    {
        $obj = SourceFactory::instance();
        $this->assertEquals('\Charcoal\Source\SourceInterface', $obj->base_class());
        $this->assertArrayHasKey('database', $obj->class_map());

    }
}
