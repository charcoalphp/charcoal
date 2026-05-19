<?php

namespace Charcoal\Tests\Admin;

// From 'charcoal-admin'
use Charcoal\Admin\Config;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ConfigTest extends AbstractTestCase
{
    public function testSetData(): void
    {
        $obj = new Config();
        $ret = $obj->merge([
            'basePath'=>'foo'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());
    }

    public function testSetBasePath(): void
    {
        $obj = new Config();
        $this->assertEquals('admin', $obj->basePath());

        $ret = $obj->setBasePath('foo');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->basePath());

        $this->expectException('\InvalidArgumentException');
        $obj->setBasePath([]);
    }

    public function testSetBasePathEmptyParamThrowsException(): void
    {
        $obj = new Config();

        $this->expectException('\InvalidArgumentException');
        $obj->setBasePath('');
    }
}
