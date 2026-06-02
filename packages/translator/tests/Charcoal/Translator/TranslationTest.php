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
    private ?\Charcoal\Translator\LocalesManager $localesManager = null;

    private function localesManager(): \Charcoal\Translator\LocalesManager
    {
        if (!$this->localesManager instanceof \Charcoal\Translator\LocalesManager) {
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

    public function testConstructorWithStringParam(): void
    {
        $obj = new Translation('Hello!', $this->localesManager());

        $this->assertEquals('Hello!', $obj['en']);
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());

        $this->assertTrue(isset($obj['en']));
        $this->assertFalse(isset($obj['fr']));
    }

    public function testConstructorWithArrayParam(): void
    {
        $obj = new Translation([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $this->localesManager());

        $this->assertEquals('Hello!', $obj['en']);
        $this->assertEquals('Bonjour!', $obj['fr']);
        $this->assertEquals([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $obj->data());

        $this->assertTrue(isset($obj['en']));
        $this->assertTrue(isset($obj['fr']));
        $this->assertFalse(isset($obj['es']));
    }

    public function testConstructorWithObjectParam(): void
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

    public function testConstructorWithInvalidParam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Translation(false, $this->localesManager());
    }

    public function testToString(): void
    {
        $manager = $this->localesManager();

        $obj = new Translation([ 'en' => 'Hello!', 'fr' => 'Bonjour!' ], $manager);

        $this->assertEquals('Hello!', (string)$obj);

        $manager->setCurrentLocale('fr');
        $this->assertEquals('Bonjour!', (string)$obj);

        unset($obj['fr']);
        $this->assertEquals('', (string)$obj);
    }

    public function testArraySet(): void
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $this->assertEquals('Hello!', (string)$obj);

        $obj['en'] = 'Charcoal';
        $this->assertEquals('Charcoal', (string)$obj);
    }

    public function testArrayGet(): void
    {
        $obj = new Translation('Charcoal', $this->localesManager());
        $this->assertEquals('Charcoal', $obj['en']);
    }

    public function testArrayUnset(): void
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $this->assertTrue(isset($obj['en']));

        unset($obj['en']);
        $this->assertFalse(isset($obj['en']));
    }

    public function testOffsetGetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj[0];
    }

    public function testOffsetGetThrowsException2(): void
    {
        $this->expectException(DomainException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj['fr'];
    }

    public function testOffsetSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj[0] = 'en';
    }

    public function testOffsetSetThrowsException2(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj['en'] = [];
    }

    public function testOffsetExistThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        $obj[0];
    }

    public function testOffsetUnsetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = new Translation('Hello!', $this->localesManager());
        unset($obj[0]);
    }

    public function testInvalidValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Translation([ 'en' ], $this->localesManager());
    }

    public function testSanitize(): void
    {
        $obj = new Translation('  Hello!  ', $this->localesManager());
        $obj->sanitize('trim');
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());
    }

    public function testEach(): void
    {
        $obj = new Translation('  Hello!  ', $this->localesManager());
        $obj->each(function ($val, $lang): string {
            $this->assertEquals('en', $lang);
            return trim($val);
        });
        $this->assertEquals([ 'en' => 'Hello!' ], $obj->data());
    }

    public function testJsonSerialize(): void
    {
        $obj = new Translation('Hello!', $this->localesManager());
        $ret = json_encode($obj);
        $this->assertEquals([ 'en' => 'Hello!' ], json_decode($ret, true));
    }
}
