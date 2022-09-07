<?php

namespace Charcoal\Tests\Translation;

use DomainException;
use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;
use Charcoal\Tests\Translator\AbstractTestCase;

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
                    'en' => [
                        'locale' => 'en_US.UTF8'
                    ],
                    'fr' => [
                        'locale' => 'fr_FR.UTF8'
                    ]
                ],
                'default_language'   => 'en',
                'fallback_languages' => [ 'en' ]

            ]);
        }

        return $this->localesManager;
    }

    /**
     * @return void
     */
    public function testConstructorWithStringParam()
    {
        $obj = new Translation('Hello!', $this->localesManager());

        $this->assertEquals('Hello!', $obj['en']);
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());

        $this->assertTrue(isset($obj['en']));
        $this->assertFalse(isset($obj['fr']));
    }

    /**
     * @return void
     */
    public function testConstructorWithArrayParam()
    {
        $obj = new Translation([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $this->localesManager());

        $this->assertEquals('Hello!', $obj['en']);
        $this->assertEquals('Bonjour!', $obj['fr']);
        $this->assertEquals([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $obj->data());

        $this->assertTrue(isset($obj['en']));
        $this->assertTrue(isset($obj['fr']));
        $this->assertFalse(isset($obj['es']));
    }

    /**
     * @return void
     */
    public function testConstructorWithObjectParam()
    {
        $trans = new Translation([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $this->localesManager());
        $obj   = new Translation($trans, $this->localesManager());

        $this->assertEquals('Hello!', $obj['en']);
        $this->assertEquals('Bonjour!', $obj['fr']);
        $this->assertEquals([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $obj->data());

        $this->assertTrue(isset($obj['en']));
        $this->assertTrue(isset($obj['fr']));
        $this->assertFalse(isset($obj['es']));
    }

    /**
     * @return void
     */
    public function testConstructorWithInvalidParam()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation(false, $this->localesManager());
    }

    /**
     * @return void
     */
    public function testToString()
    {
        $manager = $this->localesManager();

        $obj = new Translation([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $manager);

        $this->assertEquals('Hello!', (string)$obj);

        $manager->setCurrentLocale('fr');
        $this->assertEquals('Bonjour!', (string)$obj);

        unset($obj['fr']);
        $this->assertEquals('', (string)$obj);
    }

    /**
     * @return void
     */
    public function testArraySet()
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $this->assertEquals('Hello!', (string)$obj);

        $obj['en'] = 'Charcoal';
        $this->assertEquals('Charcoal', (string)$obj);
    }

    /**
     * @return void
     */
    public function testArrayGet()
    {
        $obj = new Translation('Charcoal', $this->localesManager());
        $this->assertEquals('Charcoal', $obj['en']);
    }

    /**
     * @return void
     */
    public function testArrayUnset()
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $this->assertTrue(isset($obj['en']));

        unset($obj['en']);
        $this->assertFalse(isset($obj['en']));
    }

    /**
     * @return void
     */
    public function testOffsetGetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $ret = $obj[0];
    }

    /**
     * @return void
     */
    public function testOffsetGetThrowsException2()
    {
        $this->expectException(DomainException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $ret = $obj['fr'];
    }

    /**
     * @return void
     */
    public function testOffsetSetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj[0] = 'en';
    }

    /**
     * @return void
     */
    public function testOffsetSetThrowsException2()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj['en'] = [];
    }

    /**
     * @return void
     */
    public function testOffsetExistThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        isset($obj[0]);
    }

    /**
     * @return void
     */
    public function testOffsetUnsetThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        unset($obj[0]);
    }

    /**
     * @return void
     */
    public function testInvalidValueThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation([ 'en' ], $this->localesManager());
    }

    /**
     * @return void
     */
    public function testSanitize()
    {
        $obj = new Translation('  Hello!  ', $this->localesManager());
        $obj->sanitize('trim');
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());
    }

    /**
     * @return void
     */
    public function testEach()
    {
        $obj = new Translation('  Hello!  ', $this->localesManager());
        $obj->each(function ($val, $lang) {
            $this->assertEquals('en', $lang);
            return trim($val);
        });
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());
    }

    /**
     * @return void
     */
    public function testJsonSerialize()
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $ret = json_encode($obj);
        $this->assertEquals([ 'en' => 'Hello!' ], json_decode($ret, true));
    }
}
