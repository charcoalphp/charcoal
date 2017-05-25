<?php

namespace Charcoal\Tests\Property;

use PDO;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 *
 */
class StringPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var StringProperty
     */
    public $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $this->obj = new StringProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType()
    {
        $this->assertEquals('string', $this->obj->type());
    }

    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->obj->setMultiple(false);
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());

        $this->obj->setMaxLength(20);
        $this->assertEquals('VARCHAR(20)', $this->obj->sqlType());

        $this->obj->setMaxLength(256);
        $this->assertEquals('TEXT', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    public function testSetData()
    {
        $data = [
            'min_length'  => 5,
            'max_length'  => 42,
            'regexp'      => '/[0-9]*/',
            'allow_empty' => false
        ];
        $ret = $this->obj->setData($data);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj->minLength());
        $this->assertEquals(42, $this->obj->maxLength());
        $this->assertEquals('/[0-9]*/', $this->obj->regexp());
        $this->assertEquals(false, $this->obj->allowEmpty());
    }

    public function testDisplayVal()
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];

        $this->assertEquals('', $this->obj->displayVal(null));
        $this->assertEquals('', $this->obj->displayVal(''));

        $val = [
            'en' => 'Brown fox',
            'fr' => 'Renard brun'
        ];
        $l10n = $translator->translation($val);

        $this->assertEquals($val['en'], $this->obj->displayVal($val['en']));
        $this->assertEquals($val['en'], $this->obj->displayVal($l10n));
        $this->assertEquals('Brown fox, Renard brun', $this->obj->displayVal($val));

        /** Test translatable value with a multilingual property */
        $this->obj->setL10n(true);

        $this->assertEquals('',         $this->obj->displayVal($val['en']));
        $this->assertEquals($val['en'], $this->obj->displayVal($l10n));
        $this->assertEquals($val['en'], $this->obj->displayVal($val));
        $this->assertEquals($val['fr'], $this->obj->displayVal($val, [ 'lang' => 'fr' ]));
        $this->assertEquals('',         $this->obj->displayVal($val, [ 'lang' => 'es' ]));

        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $this->assertEquals('foo, bar, baz', $this->obj->displayVal('foo,bar,baz'));
        $this->assertEquals('foo, bar, baz', $this->obj->displayVal([ 'foo', 'bar', 'baz' ]));
    }

    public function testDisplayChoices()
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];

        $choices = [
            'fox'  => $translator->translation([
                'en' => 'Brown fox',
                'fr' => 'Renard brun'
            ]),
            'dog'  => $translator->translation([
                'en' => 'Lazy dog',
                'fr' => 'Chien paresseux'
            ])/*,
            'wolf' => $translator->translation([
                'en' => 'Hungry wolf',
                'fr' => 'Loup affamé'
            ])*/
        ];
        $expected = [
            'fox' => [
                'value' => 'fox',
                'label' => $choices['fox']
            ],
            'dog' => [
                'value' => 'dog',
                'label' => $choices['dog']
            ]/*,
            'wolf' => [
                'value' => 'wolf',
                'label' => $this->translation($choices['wolf'])
            ]*/
        ];

        $this->obj->setChoices($choices);
        $this->assertEquals($expected, $this->obj->choices());

        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $this->assertEquals('Brown fox, Lazy dog, wolf', $this->obj->displayVal([ 'fox', 'dog', 'wolf' ]));
        $this->assertEquals('Brown fox, Lazy dog, wolf', $this->obj->displayVal('fox,dog,wolf'));
        $this->assertEquals('Brown fox, Lazy dog, wolf', $this->obj->displayVal('fox,dog,wolf', [ 'lang' => 'es' ]));
        $this->assertEquals('Renard brun, Chien paresseux, wolf', $this->obj->displayVal('fox,dog,wolf', [ 'lang' => 'fr' ]));
    }

    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(5, $this->obj->minLength());

        $this->obj['min_length'] = 10;
        $this->assertEquals(10, $this->obj->minLength());

        $this->obj->set('min_length', 30);
        $this->assertEquals(30, $this->obj['min_length']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMinLength('foo');
    }

    public function testSetMinLenghtNegativeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMinLength(-1);
    }

    public function testSetMaxLength()
    {
        $ret = $this->obj->setMaxLength(5);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(5, $this->obj->maxLength());

        $this->obj['max_length'] = 10;
        $this->assertEquals(10, $this->obj->maxLength());

        $this->obj->set('max_length', 30);
        $this->assertEquals(30, $this->obj['max_length']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMaxLength('foo');
    }

    public function testSetMaxLenghtNegativeThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMaxLength(-1);
    }

    public function testSetRegexp()
    {
        $ret = $this->obj->setRegexp('[a-z]');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('[a-z]', $this->obj->regexp());

        $this->obj['regexp'] = '[_]';
        $this->assertEquals('[_]', $this->obj->regexp());

        $this->obj->set('regexp', '[A-Z]');
        $this->assertEquals('[A-Z]', $this->obj['regexp']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRegexp(null);
    }

    public function testSetAllowEmpty()
    {
        $this->assertEquals(true, $this->obj->allowEmpty());

        $ret = $this->obj->setAllowEmpty(false);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(false, $this->obj->allowEmpty());

        $this->obj['allow_empty'] = true;
        $this->assertTrue($this->obj->allowEmpty());

        $this->obj->set('allow_empty', false);
        $this->assertFalse($this->obj['allow_empty']);
    }

    public function testLength()
    {
        $this->obj->setVal('foo');
        $this->assertEquals(3, $this->obj->length());

        $this->obj->setVal('a');
        $this->assertEquals(1, $this->obj->length());

        $this->obj->setVal('é');
        $this->assertEquals(1, $this->obj->length());

        $this->obj->setVal('');
        $this->assertEquals(0, $this->obj->length());

        $this->obj->setVal([ 'foo', 'baz', 'qux' ]);
        $this->assertEquals(13, $this->obj->length());
    }

    public function testValidationMethods()
    {
        $this->assertInternalType('array', $this->obj->validationMethods());
    }

    public function testValidateMaxLength()
    {
        $this->obj->setMaxLength(5);
        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('12345');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('123456789');
        $this->assertNotTrue($this->obj->validateMaxLength());

        $this->obj->setVal('Éçä˚');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('∂çäÇµ');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertNotTrue($this->obj->validateMaxLength());

        $this->obj->setVal([ 'foo', 'bar', 'qux' ]);
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal([ 'foo', 'bar', 'bazqux' ]);
        $this->assertNotTrue($this->obj->validateMaxLength());
    }

    public function testValidateMaxLengthWithZeroMaxLengthReturnsTrue()
    {
        $this->obj->setMaxLength(0);

        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMaxLength());
    }

    public function testValidateMinLength()
    {
        $this->obj->setMinLength(5);

        $this->obj->setVal('1234');
        $this->assertNotTrue($this->obj->validateMinLength());

        $this->obj->setVal('12345');
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('123456789');
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('Éçä˚');
        $this->assertNotTrue($this->obj->validateMinLength());

        $this->obj->setVal('∂çäÇµ');
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal([ 'foobar', 'barqux' ]);
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal([ 'foo', 'barbaz' ]);
        $this->assertNotTrue($this->obj->validateMinLength());
    }

    public function testValidateMinLengthAllowEmpty()
    {
        $this->obj->setAllowNull(false);
        $this->obj->setMinLength(5);
        $this->obj->setVal('');

        $this->obj->setAllowEmpty(true);
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setAllowEmpty(false);
        $this->assertNotTrue($this->obj->validateMinLength());
    }

    public function testValidateMinLengthWithoutValReturnsFalse()
    {
        $this->obj->setAllowNull(false);
        $this->obj->setMinLength(5);

        $this->assertNotTrue($this->obj->validateMinLength());
    }

    public function testValidateMinLengthWithoutMinLengthReturnsTrue()
    {
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMinLength());
    }

    public function testValidateRegexp()
    {
        /** Without RegExp */
        $this->assertTrue($this->obj->validateRegexp());

        $this->obj->setVal('123');
        $this->assertTrue($this->obj->validateRegexp());

        /** With RegExp */
        $this->obj->setRegexp('/[0-9]+/');

        $this->obj->setVal('123');
        $this->assertTrue($this->obj->validateRegexp());

        $this->obj->setVal('abc');
        $this->assertNotTrue($this->obj->validateRegexp());
    }

    public function testValidateAllowEmpty()
    {
        $this->obj->setAllowEmpty(false);

        $this->obj->setVal(null);
        $this->assertFalse($this->obj->validateAllowEmpty());

        $this->obj->setAllowEmpty(true);
        $this->assertTrue($this->obj->validateAllowEmpty());
    }
}
