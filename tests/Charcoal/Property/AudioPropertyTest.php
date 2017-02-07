<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\AudioProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class AudioPropertyTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new AudioProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
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

    public function testSetDataSnakecase()
    {
        $obj = $this->obj;
        $data = [
          'min_length' => 20,
          'max_length' => 500
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);

        $this->assertEquals(20, $obj->minLength());
        $this->assertEquals(500, $obj->maxLength());
    }

    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj->minLength());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMinLength(false);
    }

    public function testSetMaxLength()
    {
        $ret = $this->obj->setMaxLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj->maxLength());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMaxLength(false);
    }

    public function testAcceptedMimetypes()
    {
        $ret = $this->obj->acceptedMimetypes();
        $this->assertContains('audio/mp3', $ret);
        $this->assertContains('audio/mpeg', $ret);
        $this->assertContains('audio/wav', $ret);
        $this->assertContains('audio/x-wav', $ret);
    }

    /**
     * @dataProvider mimeExtensionProvider
     */
    public function testGenerateExtension($mime, $ext)
    {
        $this->obj->setMimetype($mime);
        $this->assertEquals($mime, $this->obj['mimetype']);
        $this->assertEquals($ext, $this->obj->generateExtension());
    }

    public function mimeExtensionProvider()
    {
        return [
            ['audio/mp3', 'mp3'],
            ['audio/mpeg', 'mp3'],
            ['audio/wav', 'wav'],
            ['audio/x-wav', 'wav']
        ];
    }
}
