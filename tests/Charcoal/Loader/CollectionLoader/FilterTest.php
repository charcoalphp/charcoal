<?php

namespace Charcoal\Tests\Loader\CollectionLoader;

use \Charcoal\Loader\CollectionLoader\Filter as Filter;
use \Charcoal\Charcoal as Charcoal;

class FilterTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new Filter();
		$this->assertInstanceOf('\Charcoal\Loader\CollectionLoader\Filter', $obj);

		// Default values
		$this->assertEquals('', $obj->property());
		$this->assertEquals('=', $obj->operator());
		$this->assertEquals('AND', $obj->operand());
		$this->assertEquals('', $obj->func());
	}

	public function testSetProperty()
	{
		$obj = new Filter();
		$obj->set_property('foo');

		$this->assertEquals('foo', $obj->property());
	}

	public function testSetPropertyIsChainable()
	{
		$obj = new Filter();
		$ret = $obj->set_property('foo');

		$this->assertSame($obj, $ret);
	}

	/**
	* @dataProvider providerInvalidProperties
	*/ 
	public function testSetInvalidPropertyThrowsException($property)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Filter();
		$obj->set_property($property);
	}

	public function testSetVal()
	{
		$obj = new Filter();
		$obj->set_val('1');

		$this->assertEquals('1', $obj->val());
	}

	public function testSetValIsChainable()
	{
		$obj = new Filter();
		$ret = $obj->set_val('foo');

		$this->assertSame($obj, $ret);
	}

	/**
	* @dataProvider providerValidOperators
	*/ 
	public function testSetOperator($op)
	{
		$obj = new Filter();
		$obj->set_operator($op);

		$this->assertEquals(strtoupper($op), $obj->operator());
	}

	public function testSetOperatorIsChainable()
	{
		$obj = new Filter();
		$ret = $obj->set_operator('=');

		$this->assertSame($obj, $ret);
	}

	public function testOperatorUppercase()
	{
		$obj = new Filter();
		$obj->set_operator('is null');
		$this->assertEquals('IS NULL', $obj->operator());

		$obj->set_operator('Like');
		$this->assertEquals('LIKE', $obj->operator());
	}



	/**
	* @dataProvider providerInvalidArguments
	*/ 
	public function testSetInvalidOperatorThrowsException($op)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Filter();
		$obj->set_operator($op);
	}

	/**
	* @dataProvider providerValidFuncs
	*/ 
	public function testSetFunc($func)
	{
		$obj = new Filter();
		$obj->set_func($func);

		$this->assertEquals(strtoupper($func), $obj->func());
	}

	public function testSetFuncIsChainable()
	{
		$obj = new Filter();
		$ret = $obj->set_func('abs');

		$this->assertSame($obj, $ret);
	}

	/**
	* @dataProvider providerInvalidArguments
	*/ 
	public function testSetInvalidFuncThrowsException($func)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Filter();
		$obj->set_func($func);
	}

	/**
	* @dataProvider providerValidOperands
	*/ 
	public function testSetOperandGetterUppercase($operand)
	{
		$obj = new Filter();
		$obj->set_operand($operand);

		$this->assertEquals(strtoupper($operand), $obj->operand());
	}

	/**
	* @dataProvider providerInvalidArguments
	*/ 
	public function testSetInvalidOperandThrowsException($op)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Filter();
		$obj->set_operand($op);
	}

	public function testSQLNoPropertyIsEmpty()
	{
		$obj = new Filter();
		$sql = $obj->sql();

		$this->assertEquals('', $sql);
	}

	/**
	* @dataProvider providerBasicOperators
	*/
	public function testSQLBasicOperators($operator)
	{
		$obj = new Filter();
		$obj->set_property('foo');
		$obj->set_operator($operator);
		$obj->set_val('bar');
		$sql = $obj->sql();

		// @todo: Note that 'bar' is not quoted...
		$this->assertEquals('(`foo` '.$operator.' bar)', $sql);
	}

	/**
	* @dataProvider providerNullStyleOperators
	*/
	public function testSQLNullStyleOperators($operator)
	{
		$obj = new Filter();
		$obj->set_property('foo');
		$obj->set_operator($operator);
		$obj->set_val('bar');
		$sql = $obj->sql();

		// @todo: Note that 'bar' is not quoted...
		$this->assertEquals('(`foo` '.$operator.')', $sql);
	}

	public function testSQLFunction()
	{
		$obj = new Filter();
		$obj->set_property('foo');
		$obj->set_operator('=');
		$obj->set_val('bar');
		$obj->set_func('abs');
		$sql = $obj->sql();

		// @todo: Note that 'bar' is not quoted...
		$this->assertEquals('(ABS(`foo`) = bar)', $sql);
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

	public function providerBasicOperators()
	{
		return [
			['='],
			['>'],
			['>='],
			['<'],
			['>'],
			['IS'],
			['IS NOT'],
			['LIKE']
		];
	}

	public function providerNullStyleOperators()
	{
		return [
			['IS NULL'],
			['IS NOT NULL']
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