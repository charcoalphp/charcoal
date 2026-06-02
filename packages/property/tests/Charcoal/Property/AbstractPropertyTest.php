<?php

namespace Charcoal\Tests\Property;

use Exception;
use LogicException;
use PDO;
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

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new class ([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]) extends AbstractProperty {
            public function type(): string {
                return 'test';
            }

            public function sqlType(): ?string {
                return null;
            }

            public function sqlPdoType(): int {
                return PDO::PARAM_STR;
            }
        };
    }

    public function testDefaults(): void
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

    public function testIdent(): void
    {
        $this->assertEquals('', $this->obj->ident());

        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());

        $this->obj['ident'] = 'baz';
        $this->assertEquals('baz', $this->obj->ident());

        $this->obj->set('ident', 'example');
        $this->assertEquals('example', $this->obj['ident']);
    }

    public function testL10nIdent(): void
    {
        $this->obj->setIdent('');
        $this->expectException(RuntimeException::class);
        $this->obj->l10nIdent();

        $this->obj->setL10n(true);
        $this->assertEquals('foobar_en', $this->obj->l10nIdent());
        $this->assertEquals('foobar_fr', $this->obj->l10nIdent('fr'));
        $this->assertEquals('foobar_en', $this->obj->l10nIdent());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->l10nIdent(false);
    }

    public function testL1onIdentThrowsExceptionIfL10nIsFalse(): void
    {
        $this->expectException(LogicException::class);
        $this->obj->setL10n(false);
        $this->obj->setIdent('foobar');
        $this->obj->l10nIdent();
    }

    public function testSetLabel(): void
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
     */
    public function testDisplayVal(): void
    {
        $this->assertFalse($this->obj['multiple']);
        $this->assertFalse($this->obj['l10n']);

        $this->assertEquals('', $this->obj->displayVal(null));
        $this->assertEquals('foo', $this->obj->displayVal('foo'));
    }

    public function testDisplayValL10n(): void
    {
        $this->obj['l10n'] = true;

        $this->assertFalse($this->obj['multiple']);
        $this->assertTrue($this->obj['l10n']);

        $this->assertEquals('', $this->obj->displayVal(null));
        //$this->assertEquals('foo', $this->obj->displayVal(['fr'=>'foo']));
    }

    public function testSetInputVal(): void
    {
        $this->assertEquals('', $this->obj->inputVal(null));

        $this->assertEquals('foo', $this->obj->inputVal('foo'));

        $ret = $this->obj->inputVal([ 'foo' => 'bar' ]);
        $this->assertEquals('{"foo":"bar"}', str_replace([ "\n", "\r", "\t", ' ' ], '', $ret));
    }

    public function testSetInputValL10n(): void
    {
        $this->obj->setL10n(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    public function testSetInputValMultiple(): void
    {
        $this->obj->setMultiple(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    public function testSetInputValL10nMultiple(): void
    {
        $this->obj->setL10n(true);
        $this->obj->setMultiple(true);

        $this->assertEquals('', $this->obj->inputVal(null));
        $this->assertEquals('foo', $this->obj->inputVal('foo'));
    }

    public function testSetL10n(): void
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

    public function testSetHidden(): void
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

    public function testSetMultiple(): void
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

    public function testMultipleSeparator(): void
    {
        $this->assertEquals(',', $this->obj->multipleSeparator());

        $this->obj->setMultipleOptions([
            'separator'=>'/'
        ]);
        $this->assertEquals('/', $this->obj->multipleSeparator());
    }

    public function testSetRequired(): void
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

    public function testSetUnique(): void
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

    public function testSetAllowNull(): void
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

    public function testSetStorable(): void
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

    public function testValidationMethods(): void
    {
        $this->assertIsArray($this->obj->validationMethods());
    }

    public function testSetSqlEncoding(): void
    {
        $this->assertEquals('', $this->obj->sqlEncoding());
        $ret = $this->obj->setSqlEncoding('utf8mb4');
        $this->assertSame($ret, $this->obj);

        $this->assertEquals('CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $this->obj->sqlEncoding());
    }
}
