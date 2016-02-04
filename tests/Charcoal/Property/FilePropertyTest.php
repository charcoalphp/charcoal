<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\FileProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class FilePropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new FileProperty();
        $this->assertInstanceOf('\Charcoal\Property\FileProperty', $obj);
        $this->assertEquals('uploads/', $obj->uploadPath());
        $this->assertFalse($obj->overwrite());
        $this->assertEquals([], $obj->acceptedMimetypes());
        $this->assertEquals(134220000, $obj->maxFilesize());
    }

    public function testType()
    {
        $obj = new FileProperty();
        $this->assertEquals('file', $obj->type());
    }

    public function testSetData()
    {
        $obj = new FileProperty();
        $ret = $obj->setData(
            [
            'uploadPath'=>'uploads/foobar/',
            'overwrite'=>true,
            'acceptedMimetypes'=>['image/x-foobar'],
            'maxFilesize'=>(32*1024*1024)
            ]
        );
        $this->assertSame($ret, $obj);
    }

    public function testSetUploadPath()
    {
        $obj = new FileProperty();
        $ret = $obj->setUploadPath('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar/', $obj->uploadPath());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setUploadPath(42);
    }

    public function testSetOverwrite()
    {
        $obj = new FileProperty();
        $ret = $obj->setOverwrite(true);
        $this->assertSame($ret, $obj);
        $this->assertTrue($obj->overwrite());
    }

    public function testVaidationMethods()
    {
        $obj = new FileProperty();
        $ret = $obj->validationMethods();
        $this->assertContains('accepted_mimetypes', $ret);
        $this->assertContains('max_filesize', $ret);
    }

    public function testValidateAcceptedMimetypes()
    {
        $obj = new FileProperty();
        $obj->setMimetype('image/x-foobar');
        $this->assertTrue($obj->validateAcceptedMimetypes());

        $this->assertEmpty($obj->acceptedMimetypes());
        $this->assertTrue($obj->validateAcceptedMimetypes());

        $obj->setAcceptedMimetypes(['image/x-barbaz']);
        $this->assertFalse($obj->validateAcceptedMimetypes());

        $obj->setAcceptedMimetypes(['image/x-foobar']);
        $this->assertTrue($obj->validateAcceptedMimetypes());
    }

    /**
     * @dataProvider filenameProvider
     */
    public function testSanitizeFilename($filename, $sanitized)
    {
        $obj = new FileProperty();
        $this->assertEquals($sanitized, $obj->sanitizeFilename($filename));
    }

    public function filenameProvider()
    {
        return [
            ['foobar', 'foobar'],
            ['<foo/bar*baz?x:y|z>', '_foo_bar_baz_x_y_z_'],
            ['.htaccess', 'htaccess'],
            ['../../etc/passwd', '_.._etc_passwd']
        ];
    }

    // public function testGenerateFilenameWithoutIdentThrowsException()
    // {
    //     $obj = new FileProperty();
    //     $this->setExpectedException('\Exception');
    //     $obj->generateFilename();
    // }

    public function testGenerateFilename()
    {
        $obj = new FileProperty();
        $obj->setIdent('foo');
        $ret = $obj->generateFilename();
        //$this->assertContains('Foo', $ret);
        //$this->assertContains(date('Y-m-d H:i:s'), $ret);

        $obj->setLabel('foobar');
        $ret = $obj->generateFilename();
        //$this->assertContains('foobar', $ret);
    }
}
