<?php

namespace Charcoal\Tests\Translator;

use PHPUnit_Framework_TestCase;

// From 'symfony/translation'
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;
use Charcoal\Translator\Translator;

/**
 *
 */
class TranslatorTest extends PHPUnit_Framework_TestCase
{
    private $languageManager;
    private $obj;

    public function setUp()
    {
        $this->languageManager = new LocalesManager([
            'locales' => [
                'foo' => [ 'locale' => 'foo-FOO' ],
                'bar' => [ 'locale' => 'bar-BAR' ]
            ],
            'default_language'   => 'foo',
            'fallback_languages' => ['foo']
        ]);
        $this->obj = new Translator([
            'locale'            => 'foo',
            'message_selector'  => new MessageSelector(),
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => $this->languageManager
        ]);
        $this->obj->addLoader('array', new ArrayLoader());
    }

    /**
     *
     */
    public function testTranslation()
    {
        $ret = $this->obj->translation('foo');
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foo', (string)$ret);

        $translation = clone($ret);
        $ret = $this->obj->translation($translation);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foo', (string)$ret);

        $ret = $this->obj->translation([
            'foo' => 'foobar',
            'bar' => 'barfoo'
        ]);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('foobar', (string)$ret);
    }

    /**
     * @dataProvider invalidTransTests
     */
    public function testTranslationInvalidValuesReturnNull($val)
    {
        $this->assertNull($this->obj->translation($val));
    }

    /**
     * @dataProvider validTransTests
     */
    public function testTranslate($expected, $id, $translation, $parameters, $locale, $domain)
    {
        # $this->obj->setLocale('en');
        $this->obj->addResource('array', [ (string) $id => $translation ], $locale, $domain);

        $this->assertEquals($expected, $this->obj->translate($id, $parameters, $domain, $locale));
    }

    /**
     * @dataProvider invalidTransTests
     */
    public function testTranslateInvalidValuesReturnEmptyString($val)
    {
        $this->assertEquals('', $this->obj->translate($val));
    }

    /**
     * @dataProvider validTransChoiceTests
     */
    public function testTranslateChoice($expected, $id, $translation, $number, $parameters, $locale, $domain)
    {
        # $this->obj->setLocale('en');
        $this->obj->addResource('array', [ (string) $id => $translation ], $locale, $domain);

        $this->assertEquals($expected, $this->obj->translateChoice($id, $number, $parameters, $domain, $locale));
    }

    public function testSetLocaleSetLanguageManagerCurrentLanguage()
    {
        $this->obj->setLocale('bar');
        $this->assertEquals('bar', $this->languageManager->currentLocale());
    }

    public function testLocales()
    {
        $this->assertArrayHasKey('foo', $this->obj->locales());
        $this->assertArrayHasKey('bar', $this->obj->locales());
        $this->assertArrayNotHasKey('baz', $this->obj->locales());
    }

    public function testAvailableLocales()
    {
        $this->assertEquals([ 'foo', 'bar' ], $this->obj->availableLocales());
    }

    /**
     * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
     */
    public function validTransTests()
    {
        return [
            [ 'Charcoal est super !', 'Charcoal is great!', 'Charcoal est super !', [], 'fr', '' ],
            [ 'Charcoal est awesome !', 'Charcoal is %what%!', 'Charcoal est %what% !', [ '%what%' => 'awesome' ], 'fr', '' ],
            [ 'Charcoal est super !', new StringClass('Charcoal is great!'), 'Charcoal est super !', [], 'fr', '' ],
        ];
    }

    public function invalidTransTests()
    {
        return [
            [ null ],
            [ 0 ],
            [ 1 ],
            [ true ],
            [ false ],
            [ [] ],
            [ [ 'foo', 'bar' ] ],
            [ [ [ ] ] ],
            [ '' ]
        ];
    }

    /**
     * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
     */
    public function validTransChoiceTests()
    {
        return [
            [ 'Il y a 0 pomme', '{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, [], 'fr', '' ],
            [ 'Il y a 1 pomme', '{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 1, [], 'fr', '' ],
            [ 'Il y a 10 pommes', '{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples', '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 10, [], 'fr', '' ],

            [ 'Il y a 0 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 0, [], 'fr', '' ],
            [ 'Il y a 1 pomme', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 1, [], 'fr', '' ],
            [ 'Il y a 10 pommes', 'There is one apple|There is %count% apples', 'Il y a %count% pomme|Il y a %count% pommes', 10, [], 'fr', '' ],

            [ 'Il y a 0 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 0, [], 'fr', '' ],
            [ 'Il y a 1 pomme', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 1, [], 'fr', '' ],
            [ 'Il y a 10 pommes', 'one: There is one apple|more: There is %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 10, [], 'fr', '' ],

            [ 'Il n\'y a aucune pomme', '{0} There are no apples|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 0, [], 'fr', '' ],
            [ 'Il y a 1 pomme', '{0} There are no apples|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 1, [], 'fr', '' ],
            [ 'Il y a 10 pommes', '{0} There are no apples|one: There is one apple|more: There is %count% apples', '{0} Il n\'y a aucune pomme|one: Il y a %count% pomme|more: Il y a %count% pommes', 10, [], 'fr', '' ],

            [ 'Il y a 0 pomme', new StringClass('{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples'), '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, [], 'fr', '' ],

            // Override %count% with a custom value
            [ 'Il y a quelques pommes', 'one: There is one apple|more: There are %count% apples', 'one: Il y a %count% pomme|more: Il y a %count% pommes', 2, [ '%count%' => 'quelques' ], 'fr', '' ],
        ];
    }
}

/**
 * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
 */
class StringClass
{
    protected $str;

    public function __construct($str)
    {
        $this->str = $str;
    }

    public function __toString()
    {
        return $this->str;
    }
}
