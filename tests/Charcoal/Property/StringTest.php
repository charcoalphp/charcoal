<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\String as String;

/**
* ## TODOs
* - 2015-03-12:
*/
class StringTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		mb_internal_encoding("UTF-8");
	}

	/**
	* Hello world
	*/
	public function testConstructor()
	{
		$obj = new String();
		$this->assertInstanceOf('\Charcoal\Property\String', $obj);

		$this->assertEquals(0, $obj->min_length());
		$this->assertEquals(255, $obj->max_length());
		$this->assertEquals('', $obj->regexp());

	}

	public function testSetData()
	{
		$obj = new String();
		$data = [
			'min_length'=>5,
			'max_length'=>42,
			'regexp'=>'/[0-9]*/'
		];
		$ret = $obj->set_data($data);

		$this->assertSame($ret, $obj);

		$this->assertEquals(5, $obj->min_length());
		$this->assertEquals(42, $obj->max_length());
		$this->assertEquals('/[0-9]*/', $obj->regexp());
	}

	/**
	* @dataProvider providerInvalidData
	*/
	public function testSetDataInvalidParameterThrowsException($invalid)
	{
		$this->setExpectedException('\InvalidArgumentException');
		$obj = new String();
		$obj->set_data($invalid);
	}

	public function testLength()
	{
		$obj = new String();

		$obj->set_val('');
		$this->assertEquals(0, $obj->length());

		$obj->set_val('a');
		$this->assertEquals(1, $obj->length());

		$obj->set_val('foo');
		$this->assertEquals(3, $obj->length());

		$obj->set_val('é');
		//$this->assertEquals(1, $obj->length());
	}

	public function testLengthWitoutValThrowsException()
	{
		$this->setExpectedException('\Exception');
		$obj = new String();
		$obj->length();
	}

	public function testSetMinLength()
	{
		$obj = new String();

		$obj->set_min_length(5);
		$this->assertEquals(5, $obj->min_length());
	}

	public function testSetMinLengthIsChainable()
	{
		$obj = new String();

		$ret = $obj->set_min_length(5);
		$this->assertSame($ret, $obj);
	}

	/**
	* @dataProvider providerInvalidLength
	*/
	public function testSetMinLengthInvalidParameterThrowsException($invalid)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new String();
		$obj->set_min_length($invalid);
	}
	
	public function testSetMaxLength()
	{
		$obj = new String();

		$obj->set_max_length(5);
		$this->assertEquals(5, $obj->max_length());
	}

	public function testSetMaxLengthIsChainable()
	{
		$obj = new String();

		$ret = $obj->set_max_length(5);
		$this->assertSame($ret, $obj);
	}

	/**
	* @dataProvider providerInvalidLength
	*/
	public function testSetMaxLengthInvalidParameterThrowsException($invalid)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new String();
		$obj->set_max_length($invalid);
	}

	public function testSetValidRegexp()
	{
		$obj = new String();

		$obj->set_regexp('[a-z]');
		$this->assertEquals('[a-z]', $obj->	regexp());
	}

	public function testSetRegexpIsChainable()
	{
		$obj = new String();

		$ret = $obj->set_regexp('[a-z]');
		$this->assertSame($ret, $obj);
	}

	public function testSetRegexpInvalidParameterThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new String();
		$obj->set_regexp(null);
	}

	public function testValidateMinLength()
	{
		$obj = new String();
		$obj->set_min_length(5);
		$obj->set_val('1234');
		$this->assertNotTrue($obj->validate_min_length());

		$obj->set_val('12345');
		$this->assertTrue($obj->validate_min_length());

		$obj->set_val('123456789');
		$this->assertTrue($obj->validate_min_length());
	}

	public function testValidateMinLengthUTF8()
	{
		$obj = new String();
		$obj->set_min_length(5);

		$obj->set_val('Éçä˚');
		$this->assertNotTrue($obj->validate_min_length());

		$obj->set_val('∂çäÇµ');
		$this->assertTrue($obj->validate_min_length());

		$obj->set_val('ß¨ˆ®©˜ßG');
		$this->assertTrue($obj->validate_min_length());
	}

	public function testValidateMinLengthWithoutValReturnsFalse()
	{
		$obj = new String();
		$obj->set_min_length(5);

		$this->assertNotTrue($obj->validate_min_length());
	}

	public function testValidateMinLengthWithoutMinLengthReturnsTrue()
	{
		$obj = new String();

		$this->assertTrue($obj->validate_min_length());

		$obj->set_val('1234');
		$this->assertTrue($obj->validate_min_length());
	}

	public function testValidateMaxLength()
	{
		$obj = new String();
		$obj->set_max_length(5);
		$obj->set_val('1234');
		$this->assertTrue($obj->validate_max_length());

		$obj->set_val('12345');
		$this->assertTrue($obj->validate_max_length());

		$obj->set_val('123456789');
		$this->assertNotTrue($obj->validate_max_length());
	}

	public function testValidateMaxLengthUTF8()
	{
		$obj = new String();
		$obj->set_max_length(5);

		$obj->set_val('Éçä˚');
		$this->assertTrue($obj->validate_max_length());

		$obj->set_val('∂çäÇµ');
		$this->assertTrue($obj->validate_max_length());

		$obj->set_val('ß¨ˆ®©˜ßG');
		$this->assertNotTrue($obj->validate_max_length());
	}

	/*public function testValidateMaxLengthWithoutValReturnsFalse()
	{
		$obj = new String();
		$obj->set_max_length(5);

		$this->assertNotTrue($obj->validate_max_length());
	}*/

	public function testValidateMaxLengthWithZeroMaxLengthReturnsTrue()
	{
		$obj = new String();
		$obj->set_max_length(0);

		$this->assertTrue($obj->validate_max_length());

		$obj->set_val('1234');
		$this->assertTrue($obj->validate_max_length());
	}


	public function testValidateRegexp()
	{
		$obj = new String();
		$obj->set_regexp('/[0-9*]/');

		$obj->set_val('123');
		$this->assertTrue($obj->validate_regexp());

		$obj->set_val('abc');
		$this->assertNotTrue($obj->validate_regexp());
	}

	public function testValidateRegexpEmptyRegexpReturnsTrue()
	{
		$obj = new String();
		$this->assertTrue($obj->validate_regexp());

		$obj->set_val('123');
		$this->assertTrue($obj->validate_regexp());
	}

	public function providerInvalidLength()
	{
		$obj = new \StdClass();
		return [
			[[]],
			[null],
			[true],
			[false],
			[[1, 2, 3]],
			[(-42)], // Values < 0 should not work
			['foo'],
			['42'],
			[$obj]
		];
	}

	public function providerInvalidData()
	{
		$obj = new \StdClass();
		return [
			[null],
			[true],
			[false],
			[(-42)], // Values < 0 should not work
			['foo'],
			['42'],
			[$obj]
		];
	}

}
