<?php

namespace Charcoal\Tests\Translator;

use ReflectionClass;

// From 'symfony/translation'
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Loader\ArrayLoader;

// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translation;
use Charcoal\Translator\Translator;
use Charcoal\Tests\Translator\AbstractTestCase;
use Charcoal\Tests\Translator\Mock\StringClass;

/**
 *
 */
class TranslatorTest extends AbstractTestCase
{
    /**
     * The 'symfony/config' cache factory to ignore.
     *
     * @const string
     */
    const SYMFONY_CACHE_PATH = 'vendor/symfony/config/ConfigCacheFactory.php';

    /**
     * Tested Class.
     */
    private \Charcoal\Translator\Translator $obj;

    /**
     * The language manager.
     */
    private ?\Charcoal\Translator\LocalesManager $localesManager = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new Translator([
            'locale'            => 'en',
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => $this->localesManager(),
            'message_formatter' => new MessageFormatter(),
        ]);

        $this->obj->addLoader('array', new ArrayLoader());
    }

    public static function setUpBeforeClass(): void
    {
        $path = realpath(__DIR__.'/../../../'.static::SYMFONY_CACHE_PATH);
        if ($path !== false) {
            rename($path, $path.'.txt');
        }
    }

    public static function tearDownAfterClass(): void
    {
        $path = realpath(__DIR__.'/../../../'.static::SYMFONY_CACHE_PATH.'.txt');
        if ($path !== false) {
            rename($path, str_replace('.php.txt', '.php', $path));
        }
    }

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

    public function testConstructorWithMessageFormatter(): void
    {
        $formatter  = new MessageFormatter();
        $translator = new Translator([
            'locale'            => 'en',
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => $this->localesManager(),
            'message_formatter' => $formatter,
        ]);

        $this->assertSame($formatter, $this->callMethod($translator, 'formatter'));
    }

    public function testConstructorWithoutMessageFormatter(): void
    {
        $translator = new Translator([
            'locale'            => 'en',
            'cache_dir'         => null,
            'debug'             => false,
            'manager'           => $this->localesManager(),
            'message_formatter' => null,
        ]);

        $this->assertInstanceOf(MessageFormatter::class, $this->callMethod($translator, 'formatter'));
    }

    public function testAvailableDomains(): void
    {
        $domains = $this->obj->availableDomains();
        $this->assertIsArray($domains);
        $this->assertEquals([ 'messages' ], $domains);
    }

    public function testTranslation(): void
    {
        $ret = $this->obj->translation('Hello!');
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('Hello!', (string)$ret);

        $translation = clone($ret);
        $ret = $this->obj->translation($translation);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('Hello!', (string)$ret);

        $ret = $this->obj->translation([
            'en' => 'Hello!',
            'fr' => 'Bonjour!'
        ]);
        $this->assertInstanceOf(Translation::class, $ret);
        $this->assertEquals('Hello!', (string)$ret);
    }

    /**
     *
     * @param  mixed $value The message ID.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransTests')]
    public function testTranslationInvalidValuesReturnNull(int|bool|string|array|null $value): void
    {
        $this->assertNull($this->obj->translation($value));
    }

    /**
     *
     * @param  string $expected    The expected translation.
     * @param  string $id          The message ID.
     * @param  string $translation The translation of $id.
     * @param  string $parameters  An array of parameters for the message.
     * @param  string $locale      The locale to use.
     * @param  string $domain      The domain for the message.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validTransTests')]
    public function testTranslate(string $expected, string|\Charcoal\Translator\Translation|\Charcoal\Tests\Translator\Mock\StringClass|array $id, string $translation, array $parameters, ?string $locale, string $domain): void
    {
        if (!$id instanceof Translation && !is_array($id) && $locale) {
            $this->obj->addResource('array', [ (string)$id => $translation ], $locale, $domain);
        }

        $this->assertEquals($expected, $this->obj->translate($id, $parameters, $domain, $locale));
    }

    /**
     *
     * @param  mixed $value The message ID.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransTests')]
    public function testTranslateInvalidValuesReturnEmptyString(int|bool|string|array|null $value): void
    {
        $this->assertEquals('', $this->obj->translate($value));
    }

    public function testTranslationChoice(): void
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
     *
     * @param  mixed $value The message ID.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransTests')]
    public function testTranslationChoiceInvalidValuesReturnNull(int|bool|string|array|null $value): void
    {
        $this->assertNull($this->obj->translationChoice($value, 1));
    }

    /**
     *
     * @param  string  $expected    The expected translation.
     * @param  string  $id          The message ID.
     * @param  string  $translation The translation of $id.
     * @param  integer $number      The number to use to find the indice of the message.
     * @param  string  $parameters  An array of parameters for the message.
     * @param  string  $locale      The locale to use.
     * @param  string  $domain      The domain for the message.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validTransChoiceTests')]
    public function testTranslateChoice(string $expected, string|\Charcoal\Translator\Translation|\Charcoal\Tests\Translator\Mock\StringClass|array $id, string $translation, int $number, array $parameters, ?string $locale, string $domain): void
    {
        if (!$id instanceof Translation && !is_array($id) && $locale) {
            $this->obj->addResource('array', [ (string)$id => $translation ], $locale, $domain);
        }

        $this->assertEquals($expected, $this->obj->translateChoice($id, $number, $parameters, $domain, $locale));
    }

    /**
     *
     * @param  mixed $value The message ID.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransTests')]
    public function testTranslateChoiceInvalidValuesReturnEmptyString(int|bool|string|array|null $value): void
    {
        $this->assertEquals('', $this->obj->translateChoice($value, 1));
    }

    public function testSetLocaleSetLocalesManagerCurrentLanguage(): void
    {
        $this->obj->setLocale('fr');
        $this->assertEquals('fr', $this->localesManager()->currentLocale());
    }

    public function testLocales(): void
    {
        $this->assertArrayHasKey('en', $this->obj->locales());
        $this->assertArrayHasKey('fr', $this->obj->locales());
        $this->assertArrayNotHasKey('jp', $this->obj->locales());
    }

    public function testAvailableLocales(): void
    {
        $this->assertEquals([ 'en', 'fr' ], $this->obj->availableLocales());
    }

    public function testInvalidArrayTranslation(): void
    {
        $method = $this->getMethod($this->obj, 'isValidTranslation');

        $this->assertFalse($method->invokeArgs($this->obj, [ [ 0 => 'Hello!' ] ]));
        $this->assertFalse($method->invokeArgs($this->obj, [ [ 'hello' => 0 ] ]));
    }

    public function testHasTranslation(): void
    {
        $data = [
            'en' => [
                'hello'   => 'Hello!',
                'goodbye' => 'Goodbye!',
                'super'   => 'Super!',
            ],
            'fr' => [
                'hello'   => 'Bonjour!',
                'goodbye' => 'Au revoir!',
                'great'   => 'Génial!',
            ],
            'es' => [
                'hello'   => '¡Hola!',
                'goodbye' => '¡Adiós!',
                'great'   => '¡Estupendo!',
                'super'   => '¡Súper!',
            ],
        ];

        foreach ($data as $locale => $messages) {
            $this->obj->addResource('array', $messages, $locale, 'messages');
        }

        $this->obj->setFallbackLocales([ 'es' ]);

        $this->assertTrue($this->obj->hasTrans('hello'));
        $this->assertTrue($this->obj->hasTrans('great', 'messages', 'en'));
        $this->assertFalse($this->obj->hasTrans('missing'));

        $this->assertTrue($this->obj->transExists('hello'));
        $this->assertFalse($this->obj->transExists('great', 'messages', 'en'));
        $this->assertFalse($this->obj->transExists('missing'));
    }

    /**
     * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
     */
    public function validTransTests(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            [ 'Charcoal est super !', 'Charcoal is great!', 'Charcoal est super !', [], 'fr', '' ],
            [ 'Charcoal est awesome !', 'Charcoal is %what%!', 'Charcoal est %what% !', [ '%what%' => 'awesome' ], 'fr', '' ],
            [ 'Charcoal is great!', [ 'en' => 'Charcoal is great!', 'fr' => 'Charcoal est super !'], 'Charcoal est super !', [], null, '' ],
            [ 'Charcoal est super !', new Translation([ 'en' => 'Charcoal is great!', 'fr' => 'Charcoal est super !'], $this->localesManager()), 'Charcoal est super !', [], 'fr', '' ],
            [ 'Charcoal est super !', new StringClass('Charcoal is great!'), 'Charcoal est super !', [], 'fr', '' ],
        ];
        // phpcs:enable
    }

    public static function invalidTransTests(): array
    {
        return [
            'null'                         => [ null ],
            '0'                            => [ 0 ],
            '1'                            => [ 1 ],
            'true'                         => [ true ],
            'false'                        => [ false ],
            'empty string'                 => [ '' ],
            'empty array'                  => [ [] ],
            'indexed array'                => [ [ 'foo', 'bar' ] ],
            'empty multidimensional array' => [ [ [ ] ] ],
        ];
    }

    /**
     * @link https://github.com/symfony/translation/blob/v3.2.3/Tests/TranslatorTest.php
     */
    public function validTransChoiceTests(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
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
        // phpcs:enable
    }
}
