<?php

namespace Charcoal\Tests\Property;

use Exception;
use LogicException;
use RuntimeException;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Property\ContainerIntegrationTrait;

/**
 *
 */
class AbstractPropertyTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * @var AbstractProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForAbstractClass(AbstractProperty::class, [[
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]]);
    }

    public function testDefaults()
    {
        $this->assertEquals('', $this->obj['ident']);
        $this->assertFalse($this->obj['multiple']);
        $this->assertEquals(',', $this->obj->multipleSeparator());
        $this->assertFalse($this->obj['l10n']);
        $this->assertFalse($this->obj['required']);
        $this->assertFalse($this->obj['unique']);
        $this->assertTrue($this->obj['storable']);
        $this->assertTrue($this->obj['allowNull']);
    }

    /**
     * @return void
     */
    public function testIdent()
    {
        $this->assertEquals('', $this->obj->ident());

        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());

        $this->obj['ident'] = 'baz';
        $this->assertEquals('baz', $this->obj->ident());

        $this->obj->set('ident', 'example');
        $this->assertEquals('example', $this->obj['ident']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setIdent([]);
    }

    /**
     * @return void
     */
    public function testL10nIdent()
    {
        $this->obj->setIdent('');
        $this->expectException(RuntimeException::class);
        $this->obj->l10nIdent();

        $this->obj->setL10n(true);
        $this->assertEquals('foobar_en', $this->obj->l10nIdent());
        $this->assertEquals('foobar_fr', $this->obj->l10nIdent('fr'));
        $this->assertEquals('foobar_en', $this->obj->l10nIdent(null));

        $this->expectException(InvalidArgumentException::class);
        $this->obj->l10nIdent(false);
    }

    public function testL1onIdentThrowsExceptionIfL10nIsFalse()
    {
        $this->expectException(LogicException::class);
        $this->obj->setL10n(false);
        $this->obj->setIdent('foobar');
        $this->obj->l10nIdent();
    }

    public function testSetLabel()
    {
        $this->assertEquals('', $this->obj['label']);

        $ret = $this->obj->setLabel('Foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo', $this->obj['label']);
    }

    /**
     * Asserts that the basic displayVal method:
     * - returns an empty string if the value is null
     * - returns the string as is (when not l10n / multiple)
     *
     * @return void
     */
    public function testDisplayVal()
    {
        $this->assertFalse($this->obj['multiple']);
        $this->assertFalse($this->obj['l10n']);

        $this->assertEquals('', $this->obj->displayVal(null));
        $this->assertEquals('foo', $this->obj->displayVal('foo'));
    }

    /**
     * @return void
     */
    public function testDisplayValL10n()
    {
        $this->obj['l10n'] = true;

        $this->assertFalse($this->obj['multiple']);
        $this->assertTrue($this->obj['l10n']);

        $this->assertEquals('', $this->obj->displayVal(null));
        //$this->assertEquals('foo', $this->obj->displayVal(['fr'=>'foo']));
    }

    /**
     * @return void
     */
    public function testSetInputVal()
    {
        $this->assertEquals('', $this->obj->inputVal(null));

        $this->assertEquals('foo', $this->obj->inputVal('foo'));

        $ret = $this->obj->inputVal([ 'foo' => 'bar' ]);
        $this->assertEquals('{"foo":"bar"}', str_replace([ "\n", "\r", "\t", ' ' ], '', $ret));
    }

    /**
     * @return void
     */
    public function testSetInputValL10n()
    {
        $this->obj->setL10n(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    /**
     * @return void
     */
    public function testSetInputValMultiple()
    {
        $this->obj->setMultiple(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    /**
     * @return void
     */
    public function testSetInputValL10nMultiple()
    {
        $this->obj->setL10n(true);
        $this->obj->setMultiple(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    /**
     * @return void
     */
    public function testSetL10n()
    {
        $this->assertFalse($this->obj['l10n']);

        $ret = $this->obj->setL10n(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['l10n']);

        $this->obj->setL10n(0);
        $this->assertFalse($this->obj['l10n']);

        $this->obj['l10n'] = true;
        $this->assertTrue($this->obj['l10n']);

        $this->obj->set('l10n', false);
        $this->assertFalse($this->obj['l10n']);
    }

    /**
     * @return void
     */
    public function testSetHidden()
    {
        $this->assertFalse($this->obj['hidden']);

        $ret = $this->obj->setHidden(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['hidden']);

        $this->obj->setHidden(0);
        $this->assertFalse($this->obj['hidden']);

        $this->obj['hidden'] = true;
        $this->assertTrue($this->obj['hidden']);

        $this->obj->set('hidden', false);
        $this->assertFalse($this->obj['hidden']);
    }

    /**
     * @return void
     */
    public function testSetMultiple()
    {
        $this->assertFalse($this->obj['multiple']);

        $ret = $this->obj->setMultiple(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['multiple']);

        $this->obj->setMultiple(0);
        $this->assertFalse($this->obj['multiple']);

        $this->obj['multiple'] = true;
        $this->assertTrue($this->obj['multiple']);

        $this->obj->set('multiple', false);
        $this->assertFalse($this->obj['multiple']);
    }

    /**
     * @return void
     */
    public function testMultipleSeparator()
    {
        $this->assertEquals(',', $this->obj->multipleSeparator());

        $this->obj->setMultipleOptions([
            'separator'=>'/'
        ]);
        $this->assertEquals('/', $this->obj->multipleSeparator());
    }

    /**
     * @return void
     */
    public function testSetRequired()
    {
        $this->assertFalse($this->obj['required']);

        $ret = $this->obj->setRequired(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['required']);

        $this->obj->setRequired(0);
        $this->assertFalse($this->obj['required']);

        $this->obj['required'] = true;
        $this->assertTrue($this->obj['required']);

        $this->obj->set('required', false);
        $this->assertFalse($this->obj['required']);
    }

    /**
     * @return void
     */
    public function testSetUnique()
    {
        $this->assertFalse($this->obj['unique']);

        $ret = $this->obj->setUnique(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['unique']);

        $this->obj->setUnique(0);
        $this->assertFalse($this->obj['unique']);

        $this->obj['unique'] = true;
        $this->assertTrue($this->obj['unique']);

        $this->obj->set('unique', false);
        $this->assertFalse($this->obj['unique']);
    }

    /**
     * @return void
     */
    public function testSetAllowNull()
    {
        $this->assertTrue($this->obj['allowNull']);

        $ret = $this->obj->setAllowNull(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj['allowNull']);

        $this->obj->setAllowNull(0);
        $this->assertFalse($this->obj['allowNull']);

        $this->obj['allow_null'] = true;
        $this->assertTrue($this->obj['allowNull']);

        $this->obj->set('allow_null', false);
        $this->assertFalse($this->obj['allowNull']);
    }

    /**
     * @return void
     */
    public function testSetStorable()
    {
        $this->assertTrue($this->obj['storable']);

        $ret = $this->obj->setStorable(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj['storable']);

        $this->obj->setStorable(0);
        $this->assertFalse($this->obj['storable']);

        $this->obj['storable'] = true;
        $this->assertTrue($this->obj['storable']);

        $this->obj->set('storable', false);
        $this->assertFalse($this->obj['storable']);
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
    public function testSetSqlEncoding()
    {
        $this->assertEquals('', $this->obj->sqlEncoding());
        $ret = $this->obj->setSqlEncoding('utf8mb4');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $this->obj->sqlEncoding());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlEncoding(false);
    }
}
