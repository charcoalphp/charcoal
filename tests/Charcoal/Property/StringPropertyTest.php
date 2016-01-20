<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\StringProperty as StringProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class StringPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        mb_internal_encoding('UTF-8');
    }

    /**
     * Hello world
     */
    public function testConstructor()
    {
        $obj = new StringProperty();
        $this->assertInstanceOf('\Charcoal\Property\StringProperty', $obj);

        $this->assertEquals(0, $obj->minLength());
        $this->assertEquals(255, $obj->maxLength());
        $this->assertEquals('', $obj->regexp());

    }

    public function testType()
    {
        $obj = new StringProperty();
        $this->assertEquals('string', $obj->type());
    }

    public function testSetData()
    {
        $obj = new StringProperty();
        $data = [
            'min_length'=>5,
            'max_length'=>42,
            'regexp'=>'/[0-9]*/',
            'allow_empty'=>false
        ];
        $ret = $obj->setData($data);

        $this->assertSame($ret, $obj);

        $this->assertEquals(5, $obj->minLength());
        $this->assertEquals(42, $obj->maxLength());
        $this->assertEquals('/[0-9]*/', $obj->regexp());
        $this->assertEquals(false, $obj->allowEmpty());
    }

    public function testSetMinLength()
    {
        $obj = new StringProperty();

        $ret = $obj->setMinLength(5);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5, $obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength('foo');
    }

    public function testSetMinLenghtNegativeThrowsException()
    {
        $obj = new StringProperty();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength(-1);
    }

    public function testSetMaxLength()
    {
        $obj = new StringProperty();

        $ret = $obj->setMaxLength(5);
        $this->assertSame($ret, $obj);
        $this->assertEquals(5, $obj->maxLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMaxLength('foo');
    }

    public function testSetMaxLenghtNegativeThrowsException()
    {
        $obj = new StringProperty();
        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMaxLength(-1);
    }

    public function testSetRegexp()
    {
        $obj = new StringProperty();

        $ret = $obj->setRegexp('[a-z]');
        $this->assertSame($ret, $obj);
        $this->assertEquals('[a-z]', $obj->regexp());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setRegexp(null);
    }

    public function testSetAllowEmpty()
    {
        $obj = new StringProperty();
        $this->assertEquals(true, $obj->allowEmpty());

        $ret = $obj->setAllowEmpty(false);
        $this->assertSame($ret, $obj);
        $this->assertEquals(false, $obj->allowEmpty());

    }

    public function testLength()
    {
        $obj = new StringProperty();

        $obj->setVal('');
        $this->assertEquals(0, $obj->length());

        $obj->setVal('a');
        $this->assertEquals(1, $obj->length());

        $obj->setVal('foo');
        $this->assertEquals(3, $obj->length());

        $obj->setVal('é');
        //$this->assertEquals(1, $obj->length());
    }

    public function testLengthWitoutValThrowsException()
    {
        $this->setExpectedException('\Exception');
        $obj = new StringProperty();
        $obj->length();
    }

    public function testValidateMinLength()
    {
        $obj = new StringProperty();
        $obj->setMinLength(5);
        $obj->setVal('1234');
        $this->assertNotTrue($obj->validateMinLength());

        $obj->setVal('12345');
        $this->assertTrue($obj->validateMinLength());

        $obj->setVal('123456789');
        $this->assertTrue($obj->validateMinLength());
    }

    public function testValidateMinLengthUTF8()
    {
        $obj = new StringProperty();
        $obj->setMinLength(5);

        $obj->setVal('Éçä˚');
        $this->assertNotTrue($obj->validateMinLength());

        $obj->setVal('∂çäÇµ');
        $this->assertTrue($obj->validateMinLength());

        $obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertTrue($obj->validateMinLength());
    }

    public function testValidateMinLengthAllowEmpty()
    {
        $obj = new StringProperty();
        $obj->setMinLength(5);
        $obj->setVal('');

        $obj->setAllowEmpty(true);
        $this->assertTrue($obj->validateMinLength());

        $obj->setAllowEmpty(false);
        $this->assertNotTrue($obj->validateMinLength());
    }

    public function testValidateMinLengthWithoutValReturnsFalse()
    {
        $obj = new StringProperty();
        $obj->setMinLength(5);

        $this->assertNotTrue($obj->validateMinLength());
    }

    public function testValidateMinLengthWithoutMinLengthReturnsTrue()
    {
        $obj = new StringProperty();

        $this->assertTrue($obj->validateMinLength());

        $obj->setVal('1234');
        $this->assertTrue($obj->validateMinLength());
    }

    public function testValidateMaxLength()
    {
        $obj = new StringProperty();
        $obj->setMaxLength(5);
        $obj->setVal('1234');
        $this->assertTrue($obj->validateMaxLength());

        $obj->setVal('12345');
        $this->assertTrue($obj->validateMaxLength());

        $obj->setVal('123456789');
        $this->assertNotTrue($obj->validateMaxLength());
    }

    public function testValidateMaxLengthUTF8()
    {
        $obj = new StringProperty();
        $obj->setMaxLength(5);

        $obj->setVal('Éçä˚');
        $this->assertTrue($obj->validateMaxLength());

        $obj->setVal('∂çäÇµ');
        $this->assertTrue($obj->validateMaxLength());

        $obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertNotTrue($obj->validateMaxLength());
    }

    /*public function testValidateMaxLengthWithoutValReturnsFalse()
	{
		$obj = new StringProperty();
		$obj->setMaxLength(5);

		$this->assertNotTrue($obj->validateMaxLength());
	}*/

    public function testValidateMaxLengthWithZeroMaxLengthReturnsTrue()
    {
        $obj = new StringProperty();
        $obj->setMaxLength(0);

        $this->assertTrue($obj->validateMaxLength());

        $obj->setVal('1234');
        $this->assertTrue($obj->validateMaxLength());
    }


    public function testValidateRegexp()
    {
        $obj = new StringProperty();
        $obj->setRegexp('/[0-9*]/');

        $obj->setVal('123');
        $this->assertTrue($obj->validateRegexp());

        $obj->setVal('abc');
        $this->assertNotTrue($obj->validateRegexp());
    }

    public function testValidateRegexpEmptyRegexpReturnsTrue()
    {
        $obj = new StringProperty();
        $this->assertTrue($obj->validateRegexp());

        $obj->setVal('123');
        $this->assertTrue($obj->validateRegexp());
    }

    public function testSqlType()
    {
        $obj = new StringProperty();
        $this->assertEquals('VARCHAR(255)', $obj->sqlType());

        $obj->setMaxLength(20);
        $this->assertEquals('VARCHAR(20)', $obj->sqlType());

        $obj->setMaxLength(256);
        $this->assertEquals('TEXT', $obj->sqlType());
    }

    public function testSqlTypeMultiple()
    {
        $obj = new StringProperty();
        $this->assertEquals('VARCHAR(255)', $obj->sqlType());

        $obj->setMultiple(true);
        $this->assertEquals('TEXT', $obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $obj = new StringProperty();
        $this->assertEquals(\PDO::PARAM_STR, $obj->sqlPdoType());
    }
}
