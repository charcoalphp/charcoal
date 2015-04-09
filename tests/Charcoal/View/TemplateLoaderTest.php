<?php

namespace Charcoal\Tests\View;

use \Charcoal\View\TemplateLoader as TemplateLoader;

class TemplateLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorAndDefaultValues()
    {
        $obj = new TemplateLoader;
        $this->assertInstanceOf('\Charcoal\View\TemplateLoader', $obj);

        $this->assertEquals('', $obj->ident());
        $this->assertEquals([], $obj->search_path());
    }

    public function testSetIdent()
    {
        $obj = new TemplateLoader();
        $obj->set_ident('foo');
        $this->assertEquals('foo', $obj->ident());
    }

    public function testSetIdentIsChainable()
    {
        $obj = new TemplateLoader();
        $ret = $obj->set_ident('foo');
        $this->assertSame($ret, $obj);
    }

    public function testAddPath()
    {
        $obj = new TemplateLoader();
        $obj->add_path(__DIR__);

        $this->assertEquals([__DIR__], $obj->search_path());
    }

    /**
    * @dataProvider providerInvalidDirs
    */
    public function testAddPathInvalidDirThrowsException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new TemplateLoader();
        $obj->add_path($invalid);
    }

    public function providerInvalidDirs()
    {
        $obj = new \StdClass();
        return [
            ['foo'],
            [[]],
            [''],
            [false],
            [null],
            [[1, 2, 3]],
            [0],
            [1],
            [42],
            [(-42)],
            [$obj]
        ];
    }
}
