<?php

namespace Charcoal\Tests\Helper;

use \Charcoal\Encoder\Base64\Base64Encoder as Base64Encoder;

class Base64EncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $obj = new Base64Encoder();
        $this->assertInstanceOf('\Charcoal\Encoder\Base64\Base64Encoder', $obj);
    }

    /**
    * @dataProvider providerStrings
    */
    public function testEncodeDecodeWithoutSalt($orig)
    {
        $obj = new Base64Encoder();

        $encoded = $obj->encode($orig);
        $decoded = $obj->decode($encoded);

        $this->assertEquals($decoded, $orig);
    }

    /**
    * @dataProvider providerStrings
    */
    public function testEncodeDecodeWithSalt($orig)
    {
        $obj = new Base64Encoder();

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

        $obj = new Base64Encoder();
        $obj->encode($str);
    }

    /**
    * @dataProvider providerInvalids
    */
    public function testDecodeInvalidParameterThrowsException($str)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Base64Encoder();
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
