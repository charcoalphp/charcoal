<?php

namespace Charcoal\Tests\Property;

use PDO;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class StringPropertyTest extends AbstractTestCase
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
     *
     * @return void
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

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('string', $this->obj->type());
    }

    public function testDefaults()
    {
        $this->assertFalse($this->obj['required']);
        $this->assertFalse($this->obj['unique']);
        $this->assertTrue($this->obj['storable']);
        $this->assertFalse($this->obj['l10n']);
        $this->assertFalse($this->obj['multiple']);
        $this->assertTrue($this->obj['allowNull']);
        $this->assertFalse($this->obj['allowHtml']);
        $this->assertTrue($this->obj['active']);
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $data = [
            'min_length'  => 5,
            'max_length'  => 42,
            'regexp'      => '/[0-9]*/',
            'allow_empty' => false,
            'allow_html'  => true
        ];
        $ret = $this->obj->setData($data);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj['minLength']);
        $this->assertEquals(42, $this->obj['maxLength']);
        $this->assertEquals('/[0-9]*/', $this->obj['regexp']);
        $this->assertFalse($this->obj['allowEmpty']);
        $this->assertTrue($this->obj['allowHtml']);
    }

    /**
     * @return void
     */
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

        $this->assertEquals('', $this->obj->displayVal($val['en']));
        $this->assertEquals($val['en'], $this->obj->displayVal($l10n));
        $this->assertEquals($val['en'], $this->obj->displayVal($val));
        $this->assertEquals($val['fr'], $this->obj->displayVal($val, [ 'lang' => 'fr' ]));
        $this->assertEquals('', $this->obj->displayVal($val, [ 'lang' => 'es' ]));

        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $this->assertEquals('foo, bar, baz', $this->obj->displayVal('foo,bar,baz'));
        $this->assertEquals('foo, bar, baz', $this->obj->displayVal([ 'foo', 'bar', 'baz' ]));
    }

    /**
     * @return void
     */
    public function testDisplayChoices()
    {
        $choices = $this->getDisplayChoices();
        $this->obj->setChoices($choices);

        $expected = [
            'fox' => [
                'value' => 'fox',
                'label' => $choices['fox'],
            ],
            'dog' => [
                'value' => 'dog',
                'label' => $choices['dog'],
            ],
            /*,
            'wolf' => [
                'value' => 'wolf',
                'label' => $this->translation($choices['wolf']),
            ],
            */
        ];
        $this->assertEquals($expected, $this->obj->choices());
    }

    /**
     * @used-by testDisplayChoices()
     * @used-by testRenderedDisplayChoices()
     * @return  array
     */
    public function getDisplayChoices()
    {
        $container  = $this->getContainer();
        $translator = $container['translator'];

        return [
            'fox'  => $translator->translation([
                'en' => 'Brown fox',
                'fr' => 'Renard brun',
            ]),
            'dog'  => $translator->translation([
                'en' => 'Lazy dog',
                'fr' => 'Chien paresseux',
            ]),
            /*,
            'wolf' => $translator->translation([
                'en' => 'Hungry wolf',
                'fr' => 'Loup affamé',
            ]),
            */
        ];
    }

    /**
     * @dataProvider getDisplayChoicesProvider
     *
     * @param  string $expected The displayed $value.
     * @param  mixed  $value    The value to display.
     * @param  array  $options  The display options.
     * @return void
     */
    public function testRenderedDisplayChoices($expected, $value, array $options = [])
    {
        $this->obj->setChoices($this->getDisplayChoices());
        $this->obj->setL10n(false);
        $this->obj->setMultiple(true);

        $this->assertEquals($expected, $this->obj->displayVal($value, $options));
    }

    /**
     * @used-by testRenderedDisplayChoices()
     * @return  array
     */
    public function getDisplayChoicesProvider()
    {
        return [
            [ 'Brown fox, Lazy dog, wolf',          [ 'fox', 'dog', 'wolf' ] ],
            [ 'Brown fox, Lazy dog, wolf',          'fox,dog,wolf'  ],
            [ 'Brown fox, Lazy dog, wolf',          'fox,dog,wolf', [ 'lang' => 'es' ] ],
            [ 'Renard brun, Chien paresseux, wolf', 'fox,dog,wolf', [ 'lang' => 'fr' ] ],
        ];
    }

    /**
     * @return void
     */
    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(5, $this->obj['minLength']);

        $this->obj['min_length'] = 10;
        $this->assertEquals(10, $this->obj['minLength']);

        $this->obj->set('min_length', 30);
        $this->assertEquals(30, $this->obj['min_length']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMinLength('foo');
    }

    /**
     * @return void
     */
    public function testSetMinLenghtNegativeThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->setMinLength(-1);
    }

    /**
     * @return void
     */
    public function testSetMaxLength()
    {
        $ret = $this->obj->setMaxLength(5);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(5, $this->obj['maxLength']);

        $this->obj['max_length'] = 10;
        $this->assertEquals(10, $this->obj['maxLength']);

        $this->obj->set('max_length', 30);
        $this->assertEquals(30, $this->obj['max_length']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMaxLength('foo');
    }

    /**
     * @return void
     */
    public function testSetMaxLenghtNegativeThrowsException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->setMaxLength(-1);
    }

    /**
     * @return void
     */
    public function testSetRegexp()
    {
        $ret = $this->obj->setRegexp('[a-z]');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('[a-z]', $this->obj['regexp']);

        $this->obj['regexp'] = '[_]';
        $this->assertEquals('[_]', $this->obj['regexp']);

        $this->obj->set('regexp', '[A-Z]');
        $this->assertEquals('[A-Z]', $this->obj['regexp']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setRegexp(null);
    }

    /**
     * @return void
     */
    public function testSetAllowEmpty()
    {
        $this->assertEquals(true, $this->obj['allowEmpty']);

        $ret = $this->obj->setAllowEmpty(false);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(false, $this->obj['allowEmpty']);

        $this->obj['allow_empty'] = true;
        $this->assertTrue($this->obj['allowEmpty']);

        $this->obj->set('allow_empty', false);
        $this->assertFalse($this->obj['allow_empty']);
    }

    /**
     * @return void
     */
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

    public function testParseOne()
    {
        $this->obj->setAllowHtml(false);
        $ret = $this->obj->parseOne('<p>with html</p>');
        $this->assertEquals('with html', $ret);

        $this->obj->setAllowHtml(true);
        $ret = $this->obj->parseOne('<p>with html</p>');
        $this->assertEquals('<p>with html</p>', $ret);
    }

    /**
     * @return void
     */
    public function testValidationMethods()
    {
        $this->assertInternalType('array', $this->obj->validationMethods());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testValidateMaxLengthWithZeroMaxLengthReturnsTrue()
    {
        $this->obj->setMaxLength(0);

        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMaxLength());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testValidateMinLengthWithoutValReturnsFalse()
    {
        $this->obj->setAllowNull(false);
        $this->obj->setMinLength(5);

        $this->assertNotTrue($this->obj->validateMinLength());
    }

    /**
     * @return void
     */
    public function testValidateMinLengthWithoutMinLengthReturnsTrue()
    {
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMinLength());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testValidateAllowEmpty()
    {
        $this->obj->setAllowEmpty(false);

        $this->obj->setVal(null);
        $this->assertFalse($this->obj->validateAllowEmpty());

        $this->obj->setAllowEmpty(true);
        $this->assertTrue($this->obj->validateAllowEmpty());
    }
}
