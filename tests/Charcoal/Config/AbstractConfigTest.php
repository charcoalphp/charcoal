<?php

namespace Charcoal\Tests\Config;

/**
 *
 */
class AbstractConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed The Abstract Config mock
     */
    public $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
    }

    public function testDefaults()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->defaults());
    }

    public function testMerge()
    {
        $obj = $this->obj;
        $ret = $obj->merge([
            'foo'=>'bar',
            'bar'=>'baz'
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('bar', $obj->get('foo'));
        $this->assertEquals('baz', $obj->get('bar'));
    }

    public function testData()
    {
        $obj = $this->obj;

        $c = [
            'foo'=>'bar',
            'bar'=>[
                'foobar'=>42
            ]
        ];

        $obj->merge($c);
        $this->assertSame($c, $obj->data());
    }

    /**
     * Assert that the `setSeparator` method:
     * - is chainable
     * - sets the value (retrievable with `separator()`)
     * - only accepts strings (or throws exception)
     */
    public function testSetSeparator()
    {
        $obj = $this->obj;
        $ret = $obj->setSeparator('_');
        $this->assertSame($ret, $obj);
        $this->assertEquals('_', $obj->separator());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSeparator(false);
    }

    public function testSetSeparatorWithMoreThanOneCharacterThrowsException()
    {
        $obj = $this->obj;
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setSeparator('foo');
    }

    public function testGet()
    {
        $obj = $this->obj;
        $this->assertNull($obj->get('foobar'));

        $obj->set('foobar', 42);
        $this->assertEquals(42, $obj->get('foobar'));

        $obj->set('foo', ['bar'=>666]);
        $this->assertEquals(['bar'=>666], $obj->get('foo'));
        $this->assertEquals(666, $obj->get('foo.bar'));
    }

    public function testGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->setSeparator('/');
        $this->assertEquals(42, $obj->get('foo/bar'));
    }

    public function testHas()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->has('foobar'));
        $obj['foobar'] = 42;
        $this->assertTrue($obj->has('foobar'));

        $obj['foo'] = ['bar'=>'baz'];
        $this->assertTrue($obj->has('foo.bar'));
    }


    public function testSetWithSeparator()
    {
        $obj = $this->obj;
        $obj->setSeparator('/');
        $obj->set('foo', ['a'=>'b']);
        $obj->set('foo/bar1/foo2', 'baz');

        $this->assertEquals('baz', $obj->get('foo/bar1/foo2'));
        $this->assertEquals(
            [
            'a'     => 'b',
            'bar1'  => [
            'foo2' =>'baz'
            ]],
            $obj['foo']
        );
    }

    public function testAddFileIni()
    {
        $obj = $this->obj;
        $ret = $obj->addFile(__DIR__.'/config_files/test.ini');
        $this->assertSame($ret, $obj);

        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

        $this->setExpectedException('\InvalidArgumentException');
        @$obj->addFile(__DIR__.'/config_files/invalid.ini');
    }

    public function testAddFileJson()
    {
        $obj = $this->obj;
        $ret = $obj->addFile(__DIR__.'/config_files/test.json');
        $this->assertSame($ret, $obj);

        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

        $this->setExpectedException('\Exception');
        $obj->addFile(__DIR__.'/config_files/invalid.json');
    }

    public function testAddFilePhp()
    {
        $obj = $this->obj;
        $ret = $obj->addFile(__DIR__.'/config_files/test.php');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test'=>'phpunit'], $obj['config']);
    }

    public function testAddFileYaml()
    {
        $obj = $this->obj;
        $ret = $obj->addFile(__DIR__.'/config_files/test.yml');
        $this->assertSame($ret, $obj);
        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

        $this->setExpectedException('\Exception');
        $obj->addFile(__DIR__.'/config_files/invalid.yml');
    }

    public function testLoadFileInvalidArgument()
    {
        $this->setExpectedException('\Exception');
        $this->obj->loadFile(false);
    }

    public function testLoadFileNotExist()
    {
        $this->setExpectedException('\Exception');
        $this->obj->loadFile('foo.php');
    }

    public function testLoadFileInvalidFile()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->loadFile(__DIR__.'/config_files/invalid.txt');
    }
}
