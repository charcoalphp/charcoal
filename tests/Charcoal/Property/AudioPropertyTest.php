<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\AudioProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class AudioPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var AudioProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new AudioProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testDefauls()
    {
        $this->assertEquals(0, $this->obj->minLength());
        $this->assertEquals(0, $this->obj->maxLength());
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('audio', $this->obj->type());
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj->minLength());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMinLength(false);
    }

    /**
     * @return void
     */
    public function testSetMaxLength()
    {
        $ret = $this->obj->setMaxLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj->maxLength());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setMaxLength(false);
    }

    /**
     * @return void
     */
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
     *
     * @param  string $mime A MIME type.
     * @param  string $ext  A file format.
     * @return void
     */
    public function testGenerateExtension($mime, $ext)
    {
        $this->obj->setMimetype($mime);
        $this->assertEquals($mime, $this->obj['mimetype']);
        $this->assertEquals($ext, $this->obj->generateExtension());
    }

    /**
     * @return array
     */
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
