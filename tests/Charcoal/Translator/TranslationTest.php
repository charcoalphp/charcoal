<?php

namespace Charcoal\Tests\Translation;

use InvalidArgumentException;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From `charcoal-translator`
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;

class TranslationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var LocalesManager
     */
    private $localesManager;

    private function localesManager()
    {
        if ($this->localesManager === null) {
            $this->localesManager = new LocalesManager([
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

        return $this->localesManager;
    }

    public function testConstructorWithStringParam()
    {
        $obj = new Translation('foobar', $this->localesManager());

        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals([ 'foo' => 'foobar' ], $obj->data());

        $this->assertTrue(isset($obj['foo']));
        $this->assertFalse(isset($obj['bar']));
    }

    public function testConstructorWithArrayParam()
    {
        $obj = new Translation([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $this->localesManager());

        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals('barfoo', $obj['bar']);
        $this->assertEquals([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $obj->data());

        $this->assertTrue(isset($obj['foo']));
        $this->assertTrue(isset($obj['bar']));
        $this->assertFalse(isset($obj['baz']));
    }

    public function testConstructorWithObjectParam()
    {
        $trans = new Translation([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $this->localesManager());
        $obj   = new Translation($trans, $this->localesManager());

        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals('barfoo', $obj['bar']);
        $this->assertEquals([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $obj->data());

        $this->assertTrue(isset($obj['foo']));
        $this->assertTrue(isset($obj['bar']));
        $this->assertFalse(isset($obj['baz']));
    }

    public function testConstructorWithInvalidParam()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = new Translation(false, $this->localesManager());
    }

    public function testToString()
    {
        $manager = $this->localesManager();

        $obj = new Translation([ 'foo' => 'foobar', 'bar' => 'barfoo' ], $manager);

        $this->assertEquals('foobar', (string)$obj);

        $manager->setCurrentLocale('bar');
        $this->assertEquals('barfoo', (string)$obj);

        unset($obj['bar']);
        $this->assertEquals('', (string)$obj);
    }

    public function testArraySet()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->assertEquals('foobar', (string)$obj);

        $obj['foo'] = 'Charcoal';
        $this->assertEquals('Charcoal', (string)$obj);
    }

    public function testArrayUnset()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->assertTrue(isset($obj['foo']));

        unset($obj['foo']);
        $this->assertFalse(isset($obj['foo']));
    }

    public function testOffsetGetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->setExpectedException(InvalidArgumentException::class);
        $ret = $obj[0];
    }

    public function testOffsetSetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->setExpectedException(InvalidArgumentException::class);
        $obj[0] = 'foo';
    }

    public function testOffsetSetThrowsException2()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->setExpectedException(InvalidArgumentException::class);
        $obj['foo'] = [];
    }

    public function testOffsetExistThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->setExpectedException(InvalidArgumentException::class);
        isset($obj[0]);
    }

    public function testOffsetUnsetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->setExpectedException(InvalidArgumentException::class);
        unset($obj[0]);
    }

    public function testInvalidValueThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = new Translation([ 'foo' ], $this->localesManager());
    }

    public function testJsonSerialize()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $ret = json_encode($obj);
        $this->assertEquals([ 'foo' => 'foobar' ], json_decode($ret, true));
    }
}
