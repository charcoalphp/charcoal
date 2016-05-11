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
        include_once 'AbstractEntityClass.php';
        $this->obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig');
    }

    /**
     * Asserts that passing a string argument to the constructor loads it as a config file.
     */
    public function testConstructorString()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig', [__DIR__.'/config_files/test.json']);
        $this->assertEquals(['test'=>'phpunit'], $obj['config']);
    }

    public function testConstructorArray()
    {
        $obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig', [['config'=>['foo'=>'bar']]]);
        $this->assertEquals(['foo'=>'bar'], $obj['config']);
    }

    public function testConstructorConfig()
    {
        $config = $this->obj;
        $config['foo'] = 'bar';
        $obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig', [$config]);
        $this->assertEquals('bar', $obj['foo']);
    }

    public function testConstructorInvalidParamThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig', [new \StdClass()]);
    }

    public function testConstructorDelegates()
    {
        $config = $this->obj;
        $config['foo'] = 42;
        $config['test'] = 'baz';
        $obj = $this->getMockForAbstractClass('\Charcoal\Config\AbstractConfig', [['foo'=>666], [$config]]);
        $this->assertEquals(666, $obj['foo']);
        $this->assertEquals('baz', $obj['test']);
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
    }

    public function testGetWithSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $this->assertEquals(['bar'=>42], $obj->get('foo'));
        $this->assertEquals(42, $obj->get('foo.bar'));
    }

    public function testSetWithSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo.bar', 42);
        $this->assertEquals(['bar'=>42], $obj->get('foo'));
        $this->assertEquals(42, $obj->get('foo.bar'));

        //$obj->set('foo.bar', 13);
        //$this->assertEquals(13, $obj->get('foo.bar'));

        $obj->set('foo.baz', 666);
        $this->assertEquals(42, $obj->get('foo.bar'));
        $this->assertEquals(666, $obj->get('foo.baz'));

        $obj->set('foo.x.y.z', 'test');
        $this->assertEquals('test', $obj->get('foo.x.y.z'));
    }

    public function testGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->setSeparator('/');
        $this->assertEquals(42, $obj->get('foo/bar'));
    }

    public function testOffsetGetWithCustomSeparator()
    {
        $obj = $this->obj;
        $obj->set('foo', ['bar'=>42]);
        $obj->setSeparator('/');
        $this->assertEquals(42, $obj['foo/bar']);
    }

    public function testHas()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->has('foobar'));
        $obj['foobar'] = 42;
        $this->assertTrue($obj->has('foobar'));
    }

    public function testHasWithSeparator()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->has('foo.bar'));
        $obj['foo'] = ['bar'=>'baz'];
        $this->assertTrue($obj->has('foo.bar'));
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

    /**
     * Asserts that the object can be iterated (with IteratorAggregate):
     * - The actual data is iterated (with key=>value).
     */
    public function testIterator()
    {
        $obj = $this->obj;
        $obj['foo'] = 'baz';
        $obj['bar'] = 42;

        $keys = [];
        $vals = [];
        foreach ($obj as $k => $v) {
            $keys[] = $k;
            $vals[] = $v;
        }

        $this->assertEquals(['foo','bar'], $keys);
        $this->assertEquals(['baz', 42], $vals);
    }
}
