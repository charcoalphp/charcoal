<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Source\Filter as Filter;
use \Charcoal\Charcoal as Charcoal;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new Filter();
        $this->assertInstanceOf('\Charcoal\Source\Filter', $obj);

        // Default values
        $this->assertEquals('', $obj->property());
        $this->assertEquals('=', $obj->operator());
        $this->assertEquals('AND', $obj->operand());
        $this->assertEquals('', $obj->func());
    }

    public function testSetData()
    {
        $obj = new Filter();

        $obj->setData(['property' => 'foo']);
        $this->assertEquals('foo', $obj->property());

        $obj->setData(
            [
                'property' => 'bar',
                'val' => 42
            ]
        );
        $this->assertEquals('bar', $obj->property());
        $this->assertEquals(42, $obj->val());

        // Full data et
        $data = [
            'property' => 'foo',
            'val'      => 42,
            'operator' => '=',
            'func'     => 'abs',
            'operand'  => 'and',
            'string'   => '(1=1)',
            'active'   => true
        ];
        $obj->setData($data);

        $this->assertEquals('foo', $obj->property());
        $this->assertEquals(42, $obj->val());
        $this->assertEquals('=', $obj->operator());
        $this->assertEquals('ABS', $obj->func());
        $this->assertEquals('AND', $obj->operand());
        $this->assertEquals('(1=1)', $obj->string());
        $this->assertEquals(true, $obj->active());
    }

    public function testSetDataIsChainable()
    {
        $obj = new Filter();
        $data = [
            'property' => 'foo',
            'val'      => 42,
            'operator' => '=',
            'func'     => 'abs',
            'operand'  => 'and',
            'string'   => '(1=1)',
            'active'   => true
        ];
        $ret = $obj->setData($data);
        $this->assertSame($obj, $ret);
    }

    public function testSetProperty()
    {
        $obj = new Filter();
        $obj->setProperty('foo');

        $this->assertEquals('foo', $obj->property());
    }

    public function testSetPropertyIsChainable()
    {
        $obj = new Filter();
        $ret = $obj->setProperty('foo');

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidProperties
    */
    public function testSetInvalidPropertyThrowsException($property)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Filter();
        $obj->setProperty($property);
    }

    public function testSetVal()
    {
        $obj = new Filter();
        $obj->setVal('1');

        $this->assertEquals('1', $obj->val());
    }

    public function testSetValIsChainable()
    {
        $obj = new Filter();
        $ret = $obj->setVal('foo');

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerValidOperators
    */
    public function testSetOperator($op)
    {
        $obj = new Filter();
        $obj->setOperator($op);

        $this->assertEquals(strtoupper($op), $obj->operator());
    }

    public function testSetOperatorIsChainable()
    {
        $obj = new Filter();
        $ret = $obj->setOperator('=');

        $this->assertSame($obj, $ret);
    }

    public function testOperatorUppercase()
    {
        $obj = new Filter();
        $obj->setOperator('is null');
        $this->assertEquals('IS NULL', $obj->operator());

        $obj->setOperator('Like');
        $this->assertEquals('LIKE', $obj->operator());
    }

    /**
    * @dataProvider providerInvalidArguments
    */
    public function testSetInvalidOperatorThrowsException($op)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Filter();
        $obj->setOperator($op);
    }

    /**
    * @dataProvider providerValidFuncs
    */
    public function testSetFunc($func)
    {
        $obj = new Filter();
        $obj->setFunc($func);

        $this->assertEquals(strtoupper($func), $obj->func());
    }

    public function testSetFuncIsChainable()
    {
        $obj = new Filter();
        $ret = $obj->setFunc('abs');

        $this->assertSame($obj, $ret);
    }

    /**
    * @dataProvider providerInvalidArguments
    */
    public function testSetInvalidFuncThrowsException($func)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Filter();
        $obj->setFunc($func);
    }

    /**
    * @dataProvider providerValidOperands
    */
    public function testSetOperandGetterUppercase($operand)
    {
        $obj = new Filter();
        $obj->setOperand($operand);

        $this->assertEquals(strtoupper($operand), $obj->operand());
    }

    /**
    * @dataProvider providerInvalidArguments
    */
    public function testSetInvalidOperandThrowsException($op)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Filter();
        $obj->setOperand($op);
    }

    /**
    * @dataProvider providerValidFuncs
    */
    public function testSetString($func)
    {
        $obj = new Filter();
        $obj->setString($func);

        $this->assertEquals($func, $obj->string());
    }

    public function testSetStringIsChainable()
    {
        $obj = new Filter();
        $ret = $obj->setString('and foo=1');

        $this->assertSame($obj, $ret);
    }

    public function testSetInvalidStringThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Filter();
        $obj->setString([]);
    }


    public function providerValidOperators()
    {
        return [
            ['='],
            ['>'],
            ['>='],
            ['<'],
            ['>'],
            ['IS'],
            ['IS NOT'],
            ['LIKE'],
            ['IS NULL'],
            ['IS NOT NULL'],
            ['is'], // lower case is valid
            ['Is'], // Mixed case is also valid
            ['like'],
            ['Is Not NULL']
        ];
    }

    public function providerValidOperands()
    {
        return [
            ['AND'],
            ['OR'],
            ['||'],
            ['&&'],
            ['and'],
            ['And']
        ];
    }

    public function providerValidFuncs()
    {
        return [
            ['ABS'],
            ['abs'], // lowercase is valid
            ['Abs'] // Mixed case is also valid
        ];
    }

    /**
    * Invalid arguments for operator, func and operand
    */
    public function providerInvalidProperties()
    {
        $obj = new \StdClass();
        return [
            [''], // empty string is invalid
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }

    /**
    * Invalid arguments for operator, func and operand
    */
    public function providerInvalidArguments()
    {
        $obj = new \StdClass();
        return [
            ['invalid string'],
            [''],
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }
}
