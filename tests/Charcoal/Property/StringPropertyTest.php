<?php

namespace Charcoal\Tests\Property;

use \Psr\Log\NullLogger;

use \Charcoal\Property\StringProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class StringPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringProperty $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        mb_internal_encoding('UTF-8');
        $this->obj = new StringProperty([
            'logger' => new NullLogger()
        ]);
    }

    /**
     *
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('\Charcoal\Property\StringProperty', $this->obj);

        $this->assertEquals(0, $this->obj->minLength());
        $this->assertEquals(255, $this->obj->maxLength());
        $this->assertEquals('', $this->obj->regexp());
    }

    public function testType()
    {
        $this->assertEquals('string', $this->obj->type());
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

    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(5, $this->obj->minLength());

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

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setRegexp(null);
    }

    public function testSetAllowEmpty()
    {
        $this->assertEquals(true, $this->obj->allowEmpty());

        $ret = $this->obj->setAllowEmpty(false);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(false, $this->obj->allowEmpty());
    }

    public function testLength()
    {
        $this->obj->setVal('');
        $this->assertEquals(0, $this->obj->length());

        $this->obj->setVal('a');
        $this->assertEquals(1, $this->obj->length());

        $this->obj->setVal('foo');
        $this->assertEquals(3, $this->obj->length());

        $this->obj->setVal('é');
        $this->assertEquals(1, $this->obj->length());
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
    }

    public function testValidateMinLengthUTF8()
    {

        $this->obj->setMinLength(5);

        $this->obj->setVal('Éçä˚');
        $this->assertNotTrue($this->obj->validateMinLength());

        $this->obj->setVal('∂çäÇµ');
        $this->assertTrue($this->obj->validateMinLength());

        $this->obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertTrue($this->obj->validateMinLength());
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

    public function testValidateMaxLength()
    {

        $this->obj->setMaxLength(5);
        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('12345');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('123456789');
        $this->assertNotTrue($this->obj->validateMaxLength());
    }

    public function testValidateMaxLengthUTF8()
    {

        $this->obj->setMaxLength(5);

        $this->obj->setVal('Éçä˚');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('∂çäÇµ');
        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('ß¨ˆ®©˜ßG');
        $this->assertNotTrue($this->obj->validateMaxLength());
    }

    /*public function testValidateMaxLengthWithoutValReturnsFalse()
    {

        $this->obj->setMaxLength(5);

        $this->assertNotTrue($this->obj->validateMaxLength());
    }*/

    public function testValidateMaxLengthWithZeroMaxLengthReturnsTrue()
    {

        $this->obj->setMaxLength(0);

        $this->assertTrue($this->obj->validateMaxLength());

        $this->obj->setVal('1234');
        $this->assertTrue($this->obj->validateMaxLength());
    }


    public function testValidateRegexp()
    {

        $this->obj->setRegexp('/[0-9*]/');

        $this->obj->setVal('123');
        $this->assertTrue($this->obj->validateRegexp());

        $this->obj->setVal('abc');
        $this->assertNotTrue($this->obj->validateRegexp());
    }

    public function testValidateRegexpEmptyRegexpReturnsTrue()
    {

        $this->assertTrue($this->obj->validateRegexp());

        $this->obj->setVal('123');
        $this->assertTrue($this->obj->validateRegexp());
    }

    public function testSqlType()
    {

        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());

        $this->obj->setMaxLength(20);
        $this->assertEquals('VARCHAR(20)', $this->obj->sqlType());

        $this->obj->setMaxLength(256);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlTypeMultiple()
    {

        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {

        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
