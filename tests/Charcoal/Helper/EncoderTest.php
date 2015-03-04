<?php

namespace Charcoal\Tests\Helper;

use \Charcoal\Helper\Encoder as Encoder;

class EncoderTest extends \PHPUnit_Framework_TestCase
{
	public function testContructor()
	{
		$obj = new Encoder();
		$this->assertInstanceOf('\Charcoal\Helper\Encoder', $obj);
	}

	public function testContructorInvalidTypeThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Encoder('invalid');
	}

	/**
	* @dataProvider providerInvalids
	*/
	public function testContructorInvalidParametersThrowsException()
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Encoder('invalid');
	}

	/**
	* @dataProvider providerStrings
	*/
	public function testEncodeDecodeWithoutSalt($orig)
	{
		$obj = new Encoder();

		$encoded = $obj->encode($orig);
		$decoded = $obj->decode($encoded);

		$this->assertEquals($decoded, $orig);

	}

	/**
	* @dataProvider providerStrings
	*/
	public function testEncodeDecodeWithSalt($orig)
	{
		$obj = new Encoder();

		$salt = '_s4ltZ';
		$encoded = $obj->encode($orig, $salt);
		$decoded = $obj->decode($encoded, $salt);

		$this->assertEquals($decoded, $orig);

	}

	/**
	* @dataProvider providerInvalids
	*/
	public function testEncodeInvalidParameterThrowsException($str)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Encoder();
		$obj->encode($str);
	}

	/**
	* @dataProvider providerInvalids
	*/
	public function testDecodeInvalidParameterThrowsException($str)
	{
		$this->setExpectedException('\InvalidArgumentException');

		$obj = new Encoder();
		$obj->decode($str);
	}

	public function providerStrings()
	{
		return [
			['foobar'],
			['ëncÖdéd StrÎng'],
			['']
		];
	}

	public function providerInvalids()
	{
		$obj = new \StdClass();
		return [
			[null],
			[[]],
			[0],
			[$obj]
		];
	}
}
