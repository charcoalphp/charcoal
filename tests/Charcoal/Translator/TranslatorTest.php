<?php

namespace Charcoal\Tests\Translator;

use ReflectionClass;

// From PHPUnit
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
    /**
     * The 'symfony/config' cache factory to ignore.
     *
     * @const string
     */
    const SYMFONY_CACHE_PATH = 'vendor/symfony/config/ConfigCacheFactory.php';
    /**
     * Tested Class.
     *
     * @var Translator
     */
    private $obj;

    /**
     * The language manager.
     *
     * @var LocalesManager
     */
    private $localesManager;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = new Translator([
            'locale'            => 'en',
            'message_selector'  => new MessageSelector(),
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => $this->localesManager()
        ]);

        $this->obj->addLoader('array', new ArrayLoader());
    }

    public static function setUpBeforeClass()
    {
        $path = realpath(__DIR__ . '/../../../' . static::SYMFONY_CACHE_PATH);
        if ($path !== false) {
            rename($path, $path.'.txt');
        }
    }

    public static function tearDownAfterClass()
    {
        $path = realpath(__DIR__ . '/../../../' . static::SYMFONY_CACHE_PATH . '.txt');
        if ($path !== false) {
            rename($path, str_replace('.php.txt', '.php', $path));
        }
    }

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

    public function testAvailableDomains()
    {
        $domains = $this->obj->availableDomains();
        $this->assertInternalType('array', $domains);
        $this->assertEquals([ 'messages' ], $domains);
    }

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
            'en' => 'foobar',
            'fr' => 'barfoo'
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
        if (!($id instanceof Translation || is_array($id)) && $locale) {
            $this->obj->addResource('array', [ (string)$id => $translation ], $locale, $domain);
        }

        $this->assertEquals($expected, $this->obj->translate($id, $parameters, $domain, $locale));
    }

    /**
     * @dataProvider invalidTransTests
     */
    public function testTranslateInvalidValuesReturnEmptyString($val)
    {
        $this->assertEquals('', $this->obj->translate($val));
    }

    public function testTranslationChoice()
    {
        $ret = $this->obj->translationChoice('There is one apple|There is %count% apples', 2);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('There is 2 apples', (string)$ret);

        $translation = clone($ret);
        $ret = $this->obj->translationChoice($translation, 2);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('There is 2 apples', (string)$ret);

        $ret = $this->obj->translationChoice([
            'en' => 'There is one apple|There is %count% apples',
            'fr' => 'Il y a %count% pomme|Il y a %count% pommes'
        ], 1);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('There is one apple', (string)$ret);
    }

    /**
     * @dataProvider invalidTransTests
     */
    public function testTranslationChoiceInvalidValuesReturnNull($val)
    {
        $this->assertNull($this->obj->translationChoice($val, 1));
    }

    /**
     * @dataProvider validTransChoiceTests
     */
    public function testTranslateChoice($expected, $id, $translation, $number, $parameters, $locale, $domain)
    {
        # $this->obj->setLocale('en');
        if (!($id instanceof Translation || is_array($id)) && $locale) {
            $this->obj->addResource('array', [ (string)$id => $translation ], $locale, $domain);
        }

        $this->assertEquals($expected, $this->obj->translateChoice($id, $number, $parameters, $domain, $locale));
    }

    public function testSetLocaleSetLocalesManagerCurrentLanguage()
    {
        $this->obj->setLocale('fr');
        $this->assertEquals('fr', $this->localesManager()->currentLocale());
    }

    public function testLocales()
    {
        $this->assertArrayHasKey('en', $this->obj->locales());
        $this->assertArrayHasKey('fr', $this->obj->locales());
        $this->assertArrayNotHasKey('jp', $this->obj->locales());
    }

    public function testAvailableLocales()
    {
        $this->assertEquals([ 'en', 'fr' ], $this->obj->availableLocales());
    }

    public function testInvalidArrayTranslation()
    {
        $class = new ReflectionClass($this->obj);
        $method = $class->getMethod('isValidTranslation');
        $method->setAccessible(true);

        $this->assertFalse($method->invokeArgs($this->obj, [ [ 0 => 'foo' ] ]));
        $this->assertFalse($method->invokeArgs($this->obj, [ [ 'foo' => 0 ] ]));
    }

    /**
     * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
     */
    public function validTransTests()
    {
        return [
            [ 'Charcoal est super !', 'Charcoal is great!', 'Charcoal est super !', [], 'fr', '' ],
            [ 'Charcoal est awesome !', 'Charcoal is %what%!', 'Charcoal est %what% !', [ '%what%' => 'awesome' ], 'fr', '' ],
            [ 'Charcoal is great!', [ 'en' => 'Charcoal is great!', 'fr' => 'Charcoal est super !'], 'Charcoal est super !', [], null, '' ],
            [ 'Charcoal est super !', new Translation([ 'en' => 'Charcoal is great!', 'fr' => 'Charcoal est super !'], $this->localesManager()), 'Charcoal est super !', [], 'fr', '' ],
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

            [ 'There are no appless', [ 'en' => '{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples', 'fr' => '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes' ], '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, [], null, '' ],
            [ 'Il y a 0 pomme', new Translation([ 'en' => '{0} There are no appless|{1} There is one apple|]1,Inf] There is %count% apples', 'fr' => '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes' ], $this->localesManager()), '[0,1] Il y a %count% pomme|]1,Inf] Il y a %count% pommes', 0, [], 'fr', '' ],

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
