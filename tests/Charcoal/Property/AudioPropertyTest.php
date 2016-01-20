<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\AudioProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class AudioPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Hello world
     */
    public function testConstructor()
    {
        $obj = new AudioProperty();
        $this->assertInstanceOf('\Charcoal\Property\AudioProperty', $obj);

        $this->assertEquals(0, $obj->minLength());
        $this->assertEquals(0, $obj->maxLength());
    }

    public function testType()
    {
        $obj = new AudioProperty();
        $this->assertEquals('audio', $obj->type());
    }

    public function testSetData()
    {
        $obj = new AudioProperty();
        $data = [
            'minLength'=>20,
            'maxLength'=>500
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);

        $this->assertEquals(20, $obj->minLength());
        $this->assertEquals(500, $obj->maxLength());
    }

    public function testSetMinLength()
    {
        $obj = new AudioProperty();

        $ret = $obj->setMinLength(5);
        $this->assertSame($ret, $obj);

        $this->assertEquals(5, $obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength(false);
    }

    public function testSetMaxLength()
    {
        $obj = new AudioProperty();

        $ret = $obj->setMaxLength(5);
        $this->assertSame($ret, $obj);

        $this->assertEquals(5, $obj->maxLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMaxLength(false);
    }

    /**
     * @dataProvider mimeExtensionProvider
     */
    public function testGenerateExtension($mime, $ext)
    {
        $obj = new AudioProperty();
        $obj->setMimetype($mime);
        $this->assertEquals($ext, $obj->generateExtension());
    }

    public function mimeExtensionProvider()
    {
        return [
            ['audio/mpeg', 'mp3'],
            ['audio/wav', 'wav'],
            ['audio/x-wav', 'wav']
        ];
    }
}
