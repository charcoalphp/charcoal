<?php

namespace Charcoal\Tests\Property;

use PDO;
use ReflectionClass;
use InvalidArgumentException;

// From PHP Unit
use PHPUnit_Framework_TestCase;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

/**
 *
 */
class FilePropertyTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $this->obj = new FileProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Property\FileProperty', $obj);
        $this->assertEquals('uploads/', $obj->uploadPath());
        $this->assertFalse($obj->overwrite());
        $this->assertEquals([], $obj->acceptedMimetypes());
        $this->assertEquals($obj->maxFilesizeAllowedByPhp(), $obj->maxFilesize());
    }

    /**
     * Asserts that the `type()` method is "file".
     */
    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('file', $obj->type());
    }

    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'uploadPath'        => 'uploads/foobar/',
            'overwrite'         => true,
            'acceptedMimetypes' => ['image/x-foobar'],
            'maxFilesize'       => (32*1024*1024)
        ]);
        $this->assertSame($ret, $obj);
    }

    /**
     * Asserts that the uploadPath method
     * - defaults to 'uploads/'
     * - always append a "/"
     */
    public function testSetUploadPath()
    {
        $obj = $this->obj;
        $this->assertEquals('uploads/', $this->obj->uploadPath());

        $ret = $obj->setUploadPath('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar/', $obj->uploadPath());

        $this->obj['upload_path'] = 'foo';
        $this->assertEquals('foo/', $obj->uploadPath());

        $this->obj->set('upload_path', 'bar');
        $this->assertEquals('bar/', $obj['upload_path']);

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setUploadPath(42);
    }

    public function testSetOverwrite()
    {
        $ret = $this->obj->setOverwrite(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->overwrite());

        $this->obj['overwrite'] = false;
        $this->assertFalse($this->obj->overwrite());

        $this->obj->set('overwrite', true);
        $this->assertTrue($this->obj['overwrite']);
    }

    public function testVaidationMethods()
    {
        $obj = $this->obj;
        $ret = $obj->validationMethods();
        $this->assertContains('accepted_mimetypes', $ret);
        $this->assertContains('max_filesize', $ret);
    }

    public function testValidateAcceptedMimetypes()
    {
        $obj = $this->obj;
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
     *
     */
    public function testFileExists()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->fileExists(__FILE__));

        // $this->assertTrue($obj->fileExists(strtolower(__FILE__), true));
        // $this->assertTrue($obj->fileExists(strtoupper(__FILE__), true));

        $this->assertFalse($obj->fileExists('foobar/baz/42'));
    }

    /**
     * @dataProvider providePathsForIsAbsolutePath
     */
    public function testIsAbsolutePath($path, $expectedResult)
    {
        $method = self::getMethod($this->obj, 'isAbsolutePath');
        $result = $method->invoke($this->obj, $path);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function providePathsForIsAbsolutePath()
    {
        return [
            [ '/var/lib', true ],
            [ 'c:\\\\var\\lib', true ],
            [ '\\var\\lib', true ],
            [ 'var/lib', false ],
            [ '../var/lib', false ],
            [ '', false ],
            [ null, false ],
        ];
    }

    /**
     * @dataProvider filenameProvider
     */
    public function testSanitizeFilename($filename, $sanitized)
    {
        $obj = $this->obj;
        $this->assertEquals($sanitized, $obj->sanitizeFilename($filename));
    }

    public function filenameProvider()
    {
        return [
            [ 'foobar', 'foobar' ],
            [ '<foo/bar*baz?x:y|z>', '_foo_bar_baz_x_y_z_' ],
            [ '.htaccess', 'htaccess' ],
            [ '../../etc/passwd', '_.._etc_passwd' ]
        ];
    }

    // public function testGenerateFilenameWithoutIdentThrowsException()
    // {
    //     $obj = $this->obj;
    //     $this->setExpectedException('\Exception');
    //     $obj->generateFilename();
    // }

    public function testGenerateFilename()
    {
        $obj = $this->obj;
        $obj->setIdent('foo');
        $ret = $obj->generateFilename();
        //$this->assertContains('Foo', $ret);
        //$this->assertContains(date('Y-m-d H:i:s'), $ret);

        //$obj->setLabel('foobar');
        //$ret = $obj->generateFilename();
        //$this->assertContains('foobar', $ret);
    }
}
