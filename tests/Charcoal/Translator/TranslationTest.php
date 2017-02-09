<?php

namespace Charcoal\Tests\Translation;

use PHPUnit_Framework_TestCase;

// Local Dependencies
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;

class TranslationTest extends PHPUnit_Framework_TestCase
{
    private function languageManager()
    {
        return new LocalesManager([
            'locales' => [
                'foo' => [
                    'locale' => 'foo_FOO.UTF8'
                ],
                'bar' => [
                    'locale' => 'bar_BAR.UTF8'
                ]
            ],
            'default_language'   => 'foo',
            'fallback_languages' => [ 'foo' ]

        ]);
    }

    public function testConstructorWithStringParam()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->assertEquals('foobar', (string)$obj);
        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals([ 'foo' => 'foobar' ], $obj->data());
        $this->assertTrue(isset($obj['foo']));
        $this->assertFalse(isset($obj['bar']));
    }

    public function testConstructorWithArrayParam()
    {
        $obj = new Translation([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $this->languageManager());
        $this->assertEquals('foobar', (string)$obj);
        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals('barfoo', $obj['bar']);
        $this->assertEquals([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $obj->data());
        $this->assertTrue(isset($obj['foo']));
        $this->assertTrue(isset($obj['bar']));
        $this->assertFalse(isset($obj['baz']));
    }

    public function testConstructorWithObjectParam()
    {
        $trans = new Translation([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $this->languageManager());
        $obj = new Translation($trans, $this->languageManager());

        $this->assertEquals('foobar', (string)$obj);
        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals('barfoo', $obj['bar']);
        $this->assertEquals([ 'foo' => 'foobar', 'bar' => 'barfoo'], $obj->data());
        $this->assertTrue(isset($obj['foo']));
        $this->assertTrue(isset($obj['bar']));
        $this->assertFalse(isset($obj['baz']));
    }

    public function testConstructorWithInvalidParam()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new Translation(false, $this->languageManager());
    }

    public function testArraySet()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->assertEquals('foobar', (string)$obj);
        $obj['foo'] = 'Charcoal';
        $this->assertEquals('Charcoal', (string)$obj);
    }

    public function testArrayUnset()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->assertTrue(isset($obj['foo']));
        unset($obj['foo']);
        $this->assertFalse(isset($obj['foo']));
    }

    public function testOffsetGetThrowsException()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->setExpectedException('\InvalidArgumentException');
        $ret = $obj[0];
    }

    public function testOffsetSetThrowsException()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->setExpectedException('\InvalidArgumentException');
        $obj[0] = 'foo';
    }

    public function testOffsetSetThrowsException2()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->setExpectedException('\InvalidArgumentException');
        $obj['foo'] = [];
    }

    public function testOffsetExistThrowsException()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->setExpectedException('\InvalidArgumentException');
        isset($obj[0]);
    }

    public function testOffsetUnsetThrowsException()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $this->setExpectedException('\InvalidArgumentException');
        unset($obj[0]);
    }

    public function testInvalidValueThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $obj = new Translation([ 'foo' ], $this->languageManager());
    }

    public function testJsonSerialize()
    {
        $obj = new Translation('foobar', $this->languageManager());
        $ret = json_encode($obj);
        $this->assertEquals([ 'foo' => 'foobar' ], json_decode($ret, true));
    }
}
