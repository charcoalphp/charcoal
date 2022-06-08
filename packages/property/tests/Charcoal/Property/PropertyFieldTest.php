<?php

namespace Charcoal\Tests\Property;

use PDO;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\PropertyField;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PropertyFieldTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var PropertyField
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new PropertyField([
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testData()
    {
        $data = [
            'ident'       => 'test',
            'label'       => 'Testing',
            'sqlType'     => 'VARCHAR(255)',
            'sqlPdoType'  => PDO::PARAM_STR,
            'sqlEncoding' => 'utf8mb4',
            'extra'       => 'KEY',
            'val'         => 'qux',
            'defaultVal'  => 'foo',
            'allowNull'   => false,
        ];

        $sql = '`test` VARCHAR(255) NOT NULL KEY utf8mb4 DEFAULT \'foo\' COMMENT \'Testing\'';

        $this->obj->setData($data);

        $this->assertEquals('test', $this->obj->ident());
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
        $this->assertEquals('utf8mb4', $this->obj->sqlEncoding());
        $this->assertEquals('KEY', $this->obj->extra());
        $this->assertEquals('qux', $this->obj->val());
        $this->assertEquals('foo', $this->obj->defaultVal());
        $this->assertEquals(false, $this->obj->allowNull());
        $this->assertEquals($sql, $this->obj->sql());
    }

    /**
     * @return void
     */
    public function testIdent()
    {
        $ret = $this->obj->setIdent('title');
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('title', $this->obj->ident());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setIdent(null);
    }

    /**
     * @return void
     */
    public function testSqlReturnsEmptyIfEmptyIdent()
    {
        $this->obj->setIdent('');
        $this->assertEquals('', $this->obj->sql());
    }

    /**
     * @return void
     */
    public function testLabel()
    {
        $this->assertEquals(null, $this->obj->label());

        $ret = $this->obj->setLabel('Cooking');
        $this->assertSame($this->obj, $ret);

        $label = $this->obj->label();
        $this->assertInternalType('string', $label);
        $this->assertEquals('Cooking', (string)$label);
    }

    /**
     * @return void
     */
    public function testPdoType()
    {
        $this->assertEquals(PDO::PARAM_NULL, $this->obj->sqlPdoType());

        $ret = $this->obj->setSqlPdoType(PDO::PARAM_BOOL);
        $this->assertSame($this->obj, $ret);

        $this->assertEquals(PDO::PARAM_NULL, $this->obj->sqlPdoType());

        $this->obj->setVal('foobar');
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlPdoType(null);
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $ret = $this->obj->setSqlType('INT(10)');
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('INT(10)', $this->obj->sqlType());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlType(0);
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertEquals(null, $this->obj->extra());

        $ret = $this->obj->setExtra('UNSIGNED');
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('UNSIGNED', $this->obj->extra());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setExtra(0);
    }

    /**
     * @return void
     */
    public function testSqlEncoding()
    {
        $this->assertEquals(null, $this->obj->sqlEncoding());

        $ret = $this->obj->setSqlEncoding('UNSIGNED');
        $this->assertSame($this->obj, $ret);

        $this->assertEquals('UNSIGNED', $this->obj->sqlEncoding());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setSqlEncoding(0);
    }
}
