<?php

namespace Charcoal\Tests\Property;

use PDO;
use DomainException;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\IdProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class IdPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var IdProperty
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new IdProperty([
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
        $this->assertEquals('id', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $ret = $this->obj->setData(
            [
            'mode'=>'uniqid'
            ]
        );
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('uniqid', $this->obj['mode']);
    }

    /**
     * Asserts that the default mode:
     * - Defaults to auto-increment
     *
     * @return void
     */
    public function testDefaultMode()
    {
        $this->assertEquals(IdProperty::DEFAULT_MODE, $this->obj['mode']);
        $this->assertEquals('auto-increment', $this->obj['mode']);
    }

    /**
     * Asserts that the `setMode` method:
     * - is chainable
     * - properly sets the mode
     * - throws an invalid argument exception for any string modes
     *
     * @return void
     */
    public function testSetMode()
    {
        $ret = $this->obj->setMode('uuid');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('uuid', $this->obj['mode']);

        $this->obj['mode'] = 'auto-increment';
        $this->assertEquals('auto-increment', $this->obj['mode']);

        $this->obj->set('mode', 'uniqid');
        $this->assertEquals('uniqid', $this->obj['mode']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMode('foo');
    }

    /**
     * Asserts that calling the `setMode()` method with a NULL argument:
     * - is chainable
     * - properly resets the mode to detault
     *
     * @return void
     */
    public function testSetModeNullThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMode(null);
    }

    /**
     * @return void
     */
    public function testMultipleCannotBeTrue()
    {
        $this->assertFalse($this->obj['multiple']);

        $this->assertSame($this->obj, $this->obj->setMultiple(false));
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMultiple(true);
    }

    /**
     * @return void
     */
    public function testL10nCannotBeTrue()
    {
        $this->assertFalse($this->obj['l10n']);

        $this->assertSame($this->obj, $this->obj->setL10n(false));
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setL10n(true);
    }

    /**
     * @return void
     */
    public function testSaveAndAutoGenerateAutoIncrement()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $id = $obj->save(null);
        $this->assertEquals('', $id);
    }

    /**
     * @return void
     */
    public function testSaveAndAutoGenerateUniqid()
    {
        $obj = $this->obj;
        $obj->setMode('uniqid');
        $id = $obj->save(null);
        $this->assertEquals(13, strlen($id));
    }

    /**
     * @return void
     */
    public function testSaveAndAutoGenerateUuid()
    {
        $obj = $this->obj;
        $obj->setMode('uuid');
        $id = $obj->save(null);
        $this->assertEquals(36, strlen($id));
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlExtra();
        $this->assertEquals('AUTO_INCREMENT', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlExtra();
        $this->assertEquals('', $ret);
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $container = $this->getContainer();

        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlType();
        //$this->assertEquals('INT(10) UNSIGNED', $ret);
        $this->assertEquals('INT', $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(13)', $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlType();
        $this->assertEquals('CHAR(36)', $ret);

        $obj->setMode('custom');
        $ret = $obj->sqlType();
        $this->assertEquals('VARCHAR(255)', $ret);
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $obj = $this->obj;
        $obj->setMode('auto-increment');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_INT, $ret);

        $obj->setMode('uniqid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_STR, $ret);

        $obj->setMode('uuid');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_STR, $ret);

        $obj->setMode('custom');
        $ret = $obj->sqlPdoType();
        $this->assertEquals(PDO::PARAM_STR, $ret);
    }
}
