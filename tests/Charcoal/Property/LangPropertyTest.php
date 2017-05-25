<?php

namespace Charcoal\Tests\Property;

use PDO;
use ReflectionClass;

// From 'charcoal-property'
use Charcoal\Property\LangProperty;

/**
 * Lang Property Test
 */
class LangPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var LangProperty
     */
    public $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $this->obj = new LangProperty([
            'container'  => $container,
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('lang', $this->obj->type());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->obj->setMultiple(false);
        $this->assertEquals('CHAR(2)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());
    }

    public function testChoices()
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];

        $this->assertTrue($this->obj->hasChoices());

        $locales = $translator->locales();
        $choices = $this->obj->choices();

        $this->assertEquals(array_keys($locales), array_keys($choices));

        $this->obj->addChoice('x', 'en');
        $this->obj->addChoices([ 'y' => 'en' ]);
        $this->obj->setChoices([ 'z' => 'en' ]);
        $this->assertEquals(array_keys($locales), array_keys($choices));
    }

    public function testDisplayVal()
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];

        $this->assertEquals('', $this->obj->displayVal(null));
        $this->assertEquals('', $this->obj->displayVal(''));

        $this->assertEquals('English', $this->obj->displayVal('en'));
        $this->assertEquals('Anglais', $this->obj->displayVal('en', [ 'lang' => 'fr' ]));

        $val = $translator->translation('en');
        /** Test translatable value with a unilingual property */
        $this->assertEquals('English', $this->obj->displayVal($val));

        /** Test translatable value with a multilingual property */
        $this->obj->setL10n(true);

        $this->assertEquals('',        $this->obj->displayVal('foo'));
        $this->assertEquals('',        $this->obj->displayVal($val, [ 'lang' => 'ja' ]));
        $this->assertEquals('Inglés',  $this->obj->displayVal($val, [ 'lang' => 'es' ]));
        $this->assertEquals('Anglais', $this->obj->displayVal($val, [ 'lang' => 'fr' ]));
        $this->assertEquals('English', $this->obj->displayVal($val, [ 'lang' => 'de' ]));
        $this->assertEquals('English', $this->obj->displayVal($val));

        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $this->assertEquals('English, French, ES',   $this->obj->displayVal([ 'en', 'fr', 'es' ]));
        $this->assertEquals('Anglais, Français, ES', $this->obj->displayVal('en,fr,es', [ 'lang' => 'fr' ]));
        $this->assertEquals('Inglés, Francés, ES',   $this->obj->displayVal('en,fr,es', [ 'lang' => 'es' ]));
    }
}
