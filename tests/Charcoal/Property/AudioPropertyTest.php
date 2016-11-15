<?php

namespace Charcoal\Tests\Property;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\AudioProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class AudioPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new AudioProperty([
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger()
        ]);
    }

    public function testDefauls()
    {
        $this->assertEquals(0, $this->obj->minLength());
        $this->assertEquals(0, $this->obj->maxLength());
    }

    public function testType()
    {
        $this->assertEquals('audio', $this->obj->type());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $data = [
            'minLength' => 20,
            'maxLength' => 500
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);

        $this->assertEquals(20, $obj->minLength());
        $this->assertEquals(500, $obj->maxLength());
    }

    public function testSetMinLength()
    {
        $obj = $this->obj;

        $ret = $obj->setMinLength(5);
        $this->assertSame($ret, $obj);

        $this->assertEquals(5, $obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMinLength(false);
    }

    public function testSetMaxLength()
    {
        $obj = $this->obj;

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
        $obj = $this->obj;
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
