<?php

namespace Charcoal\Tests\Translation;

use DomainException;
use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class TranslationTest extends AbstractTestCase
{
    /**
     * @var LocalesManager
     */
    private $localesManager;

    /**
     * @return LocalesManager
     */
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

    /**
     * @return void
     */
    public function testConstructorWithStringParam()
    {
        $obj = new Translation('foobar', $this->localesManager());

        $this->assertEquals('foobar', $obj['foo']);
        $this->assertEquals([ 'foo' => 'foobar' ], $obj->data());

        $this->assertTrue(isset($obj['foo']));
        $this->assertFalse(isset($obj['bar']));
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testConstructorWithInvalidParam()
    {
        $obj = new Translation(false, $this->localesManager());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testArraySet()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->assertEquals('foobar', (string)$obj);

        $obj['foo'] = 'Charcoal';
        $this->assertEquals('Charcoal', (string)$obj);
    }

    /**
     * @return void
     */
    public function testArrayGet()
    {
        $obj = new Translation('Charcoal', $this->localesManager());
        $this->assertEquals('Charcoal', $obj['foo']);
    }

    /**
     * @return void
     */
    public function testArrayUnset()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $this->assertTrue(isset($obj['foo']));

        unset($obj['foo']);
        $this->assertFalse(isset($obj['foo']));
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testOffsetGetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $ret = $obj[0];
    }

    /**
     * @expectedException DomainException
     *
     * @return void
     */
    public function testOffsetGetThrowsException2()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $ret = $obj['bar'];
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testOffsetSetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $obj[0] = 'foo';
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testOffsetSetThrowsException2()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $obj['foo'] = [];
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testOffsetExistThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        isset($obj[0]);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testOffsetUnsetThrowsException()
    {
        $obj = new Translation('foobar', $this->localesManager());
        unset($obj[0]);
    }

    /**
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testInvalidValueThrowsException()
    {
        $obj = new Translation([ 'foo' ], $this->localesManager());
    }

    /**
     * @return void
     */
    public function testSanitize()
    {
        $obj = new Translation('  foobar  ', $this->localesManager());
        $obj->sanitize('trim');
        $this->assertEquals([ 'foo' => 'foobar' ], $obj->data());
    }

    /**
     * @return void
     */
    public function testJsonSerialize()
    {
        $obj = new Translation('foobar', $this->localesManager());
        $ret = json_encode($obj);
        $this->assertEquals([ 'foo' => 'foobar' ], json_decode($ret, true));
    }
}
