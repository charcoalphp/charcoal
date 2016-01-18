<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Source\SourceFactory;

class SourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultData()
    {
        $factory = new SourceFactory();
        $this->assertEquals('\Charcoal\Source\SourceInterface', $factory->baseClass());
        $this->assertArrayHasKey('database', $factory->map());
    }
}
