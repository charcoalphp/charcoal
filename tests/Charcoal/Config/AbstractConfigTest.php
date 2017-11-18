<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use Exception;
use InvalidArgumentException;

use Charcoal\Config\AbstractConfig;

/**
 *
 */
class AbstractConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var mixed The Abstract Config mock
     */
    public $obj;

    public function setUp()
    {
        include_once 'AbstractEntityClass.php';
        $this->obj = $this->getMockForAbstractClass(AbstractConfig::class);
    }

    /**
     * Asserts that passing a string argument to the constructor loads it as a config file.
     */
    public function testConstructorString()
    {
        $obj = $this->getMockForAbstractClass(AbstractConfig::class, [__DIR__.'/config_files/test.json']);
        $this->assertEquals(['test'=>'phpunit'], $obj['config']);
    }

    public function testConstructorArray()
    {
        $obj = $this->getMockForAbstractClass(AbstractConfig::class, [['config'=>['foo'=>'bar']]]);
        $this->assertEquals(['foo'=>'bar'], $obj['config']);
    }

    public function testConstructorConfig()
    {
        $config = $this->obj;
        $config['foo'] = 'bar';
        $obj = $this->getMockForAbstractClass(AbstractConfig::class, [$config]);
        $this->assertEquals('bar', $obj['foo']);
    }

    public function testConstructorInvalidParamThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = $this->getMockForAbstractClass(AbstractConfig::class, [new \StdClass()]);
    }



    public function testDefaults()
    {
        $obj = $this->obj;
        $this->assertEquals([], $obj->defaults());
    }

    public function testMerge()
    {
        $this->obj->setData([
            'a' => 1,
            'b' => [1, 2, 3],
            'c' => [
                'd' => 7,
                'e' => [7,8,9]
            ],
            'd' => 'foo'
        ]);

        $ret = $this->obj->merge([
            'a' => 2,
            'b' => [4, 5, 6],
            'c' => [
                'a' => 42,
                'd' => 67
            ]
        ]);
        $this->assertSame($ret, $this->obj);

        $expected = [
            'a' => 2,
            'b' => [4, 5, 6],
            'c' => [
                'd' => 67,
                'e' => [7, 8, 9],
                'a' => 42
            ],
            'd' => 'foo'

        ];

        $this->assertEquals($expected, $this->obj->data());
    }

    public function testMergeWithConfigInterface()
    {
        $this->obj ->setData([
            'a' => 1,
            'b' => [1, 2, 3],
            'c' => [
                'd' => 7,
                'e' => [7,8,9]
            ],
            'd' => 'foo'
        ]);

        $config = $this->getMockForAbstractClass(AbstractConfig::class, [[
            'a' => 2,
            'b' => [4, 5, 6],
            'c' => [
                'a' => 42,
                'd' => 67
            ]
        ]]);
        $this->obj->merge($config);

        $expected = [
            'a' => 2,
            'b' => [4, 5, 6],
            'c' => [
                'd' => 67,
                'e' => [7, 8, 9],
                'a' => 42
            ],
            'd' => 'foo'

        ];

        $this->assertEquals($expected, $this->obj->data());
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


    public function testGet()
    {
        $obj = $this->obj;
        $this->assertNull($obj->get('foobar'));

        $obj->set('foobar', 42);
        $this->assertEquals(42, $obj->get('foobar'));
    }

    public function testSetGetUnderscore()
    {
        $this->obj->set('_', 'test');
        $this->assertNull($this->obj->get('_'));
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

        $this->setExpectedException(InvalidArgumentException::class);
        @$obj->addFile(__DIR__.'/config_files/invalid.ini');
    }

    public function testAddFileJson()
    {
        $obj = $this->obj;
        $ret = $obj->addFile(__DIR__.'/config_files/test.json');
        $this->assertSame($ret, $obj);

        $this->assertEquals(['test'=>'phpunit'], $obj['config']);

        $this->setExpectedException(Exception::class);
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

        $this->setExpectedException(Exception::class);
        $obj->addFile(__DIR__.'/config_files/invalid.yml');
    }

    public function testLoadFileInvalidArgument()
    {
        $this->setExpectedException(Exception::class);
        $this->obj->loadFile(false);
    }

    public function testLoadFileNotExist()
    {
        $this->setExpectedException(Exception::class);
        $this->obj->loadFile('foo.php');
    }

    public function testLoadFileInvalidFile()
    {
        $this->setExpectedException(InvalidArgumentException::class);
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
