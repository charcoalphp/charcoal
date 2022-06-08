<?php

namespace Charcoal\Tests\Property;

use PDO;
use InvalidArgumentException;
use ReflectionClass;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorInterface as Validator;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

/**
 *
 */
class FilePropertyTest extends AbstractFilePropertyTestCase
{
    /**
     * Create a file property instance.
     *
     * @return FileProperty
     */
    public function createProperty()
    {
        $container = $this->getContainer();

        return new FileProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
            'container'  => $container,
        ]);
    }

    /**
     * Asserts that the `type()` method is "file".
     *
     * @covers \Charcoal\Property\FileProperty::type()
     * @return void
     */
    public function testPropertyType()
    {
        $this->assertEquals('file', $this->obj->type());
    }

    /**
     * Asserts that the property adheres to file property defaults.
     *
     * @covers \Charcoal\Property\FileProperty::getDefaultAcceptedMimetypes()
     * @return void
     */
    public function testDefaulAcceptedMimeTypes()
    {
        $this->assertInternalType('array', $this->obj['defaultAcceptedMimetypes']);
        $this->assertEmpty($this->obj['defaultAcceptedMimetypes']);
    }

    /**
     * Asserts that the property properly checks if
     * any acceptable MIME types are available.
     *
     * @covers \Charcoal\Property\FileProperty::hasAcceptedMimetypes()
     * @return void
     */
    public function testHasAcceptedMimeTypes()
    {
        $obj = $this->obj;

        $explicitMimeTypes = $this->getPropertyValue($obj, 'acceptedMimetypes');
        $fallbackMimeTypes = $obj->getDefaultAcceptedMimetypes();
        if (!empty($explicitMimeTypes) || !empty($fallbackMimeTypes)) {
            $this->assertTrue($obj->hasAcceptedMimetypes());
        } else {
            $this->assertFalse($obj->hasAcceptedMimetypes());
        }

        if (empty($explicitMimeTypes)) {
            $obj->setAcceptedMimetypes([ 'text/plain', 'text/html', 'text/css' ]);
            $this->assertTrue($obj->hasAcceptedMimetypes());
        }
    }

    /**
     * Asserts that the property can resolve a filesize from its value.
     *
     * @return void
     */
    public function testFilesizeFromVal()
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/document.txt');

        $this->assertEquals(743, $obj['filesize']);
    }

    /**
     * Asserts that the property can resolve a MIME type from its value.
     *
     * @return void
     */
    public function testMimetypeFromVal()
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/document.txt');

        $this->assertEquals('text/plain', $obj['mimetype']);
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'public_access'     => true,
            'uploadPath'        => 'uploads/foobar/',
            'overwrite'         => true,
            'acceptedMimetypes' => [ 'image/x-foobar' ],
            'maxFilesize'       => (32 * 1024 * 1024),
        ]);
        $this->assertSame($ret, $obj);

        $this->assertTrue($this->obj['publicAccess']);
        $this->assertEquals('uploads/foobar/', $this->obj['uploadPath']);
        $this->assertTrue($this->obj['overwrite']);
        $this->assertEquals(['image/x-foobar'], $this->obj['acceptedMimetypes']);
        $this->assertEquals((32 * 1024 * 1024), $this->obj['maxFilesize']);
    }

    /**
     * @return void
     */
    public function testSetOverwrite()
    {
        $ret = $this->obj->setOverwrite(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['overwrite']);

        $this->obj['overwrite'] = false;
        $this->assertFalse($this->obj['overwrite']);

        $this->obj->set('overwrite', true);
        $this->assertTrue($this->obj['overwrite']);
    }

    /**
     * @return void
     */
    public function testVaidationMethods()
    {
        $methods = $this->obj->validationMethods();
        $this->assertContains('mimetypes', $methods);
        $this->assertContains('filesizes', $methods);
    }

    /**
     * Test validation file MIME types on property.
     *
     * @dataProvider provideDataForValidateMimetypes
     *
     * @param  mixed   $val               The value(s) to be validated.
     * @param  boolean $l10n              Whether the property value is multilingual.
     * @param  boolean $multiple          Whether the property accepts zero or more values.
     * @param  mixed   $acceptedMimetypes The accepted MIME types.
     * @param  boolean $expectedReturn    The expected return value of the method.
     * @param  array   $expectedResults   The expected validation results.
     * @return void
     */
    public function testValidateMimetypes(
        $val,
        $l10n,
        $multiple,
        $acceptedMimetypes,
        $expectedReturn,
        array $expectedResults = []
    ) {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['acceptedMimetypes'] = $acceptedMimetypes;
        $obj['l10n'] = $l10n;
        $obj['multiple'] = $multiple;
        $obj['val'] = $val;

        $this->assertSame($expectedReturn, $obj->validateMimetypes());

        $this->assertValidatorHasResults(
            $expectedResults,
            $obj->validator()->results()
        );
    }

    /**
     * Test validation file sizes on property.
     *
     * @dataProvider provideDataForValidateFilesizes
     *
     * @param  mixed   $val             The value(s) to be validated.
     * @param  boolean $l10n            Whether the property value is multilingual.
     * @param  boolean $multiple        Whether the property accepts zero or more values.
     * @param  integer $maxFilesize     The maximum file size accepted.
     * @param  boolean $expectedReturn  The expected return value of the method.
     * @param  array   $expectedResults The expected validation results.
     * @return void
     */
    public function testValidateFilesizes(
        $val,
        $l10n,
        $multiple,
        $maxFilesize,
        $expectedReturn,
        array $expectedResults = []
    ) {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['maxFilesize'] = $maxFilesize;
        $obj['l10n'] = $l10n;
        $obj['multiple'] = $multiple;
        $obj['val'] = $val;

        $this->assertSame($expectedReturn, $obj->validateFilesizes());

        $this->assertValidatorHasResults(
            $expectedResults,
            $obj->validator()->results()
        );
    }

    /**
     * @return void
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
     *
     * @param  string $path     A path to test.
     * @param  string $expected Whether the path is absolute (TRUE) or relative (FALSE).
     * @return void
     */
    public function testIsAbsolutePath($path, $expected)
    {
        $result = $this->callMethodWith($this->obj, 'isAbsolutePath', $path);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function providePathsForIsAbsolutePath()
    {
        return [
            [ '/var/lib',       true  ],
            [ 'c:\\\\var\\lib', true  ],
            [ '\\var\\lib',     true  ],
            [ 'var/lib',        false ],
            [ '../var/lib',     false ],
            [ '',               false ],
            [ null,             false ],
        ];
    }

    /**
     * @dataProvider filenameProvider
     *
     * @param  string $filename  A dirty filename.
     * @param  string $sanitized A clean version of $filename.
     * @return void
     */
    public function testSanitizeFilename($filename, $sanitized)
    {
        $obj = $this->obj;
        $this->assertEquals($sanitized, $obj->sanitizeFilename($filename));
    }

    /**
     * @return array
     */
    public function filenameProvider()
    {
        return [
            [ 'foobar',              'foobar'              ],
            [ '<foo/bar*baz?x:y|z>', '_foo_bar_baz_x_y_z_' ],
            [ '.htaccess',           'htaccess'            ],
            [ '../../etc/passwd',    '_.._etc_passwd'      ],
        ];
    }

    /**
     * @return void
     */
    public function testGenerateFilename()
    {
        $obj = $this->obj;
        $obj->setIdent('foo');
        $ret = $obj->generateFilename();
        $this->assertContains('Foo', $ret);
        $this->assertContains(date('Y-m-d'), $ret);

        //$obj->setLabel('foobar');
        //$ret = $obj->generateFilename();
        //$this->assertContains('foobar', $ret);
    }

    public function testGenerateUniqueFilename()
    {
        $ret = $this->obj->generateUniqueFilename('foo.png');
        $this->assertContains('foo', $ret);
        $this->assertStringEndsWith('.png', $ret);
        $this->assertNotEquals($ret, 'foo');
    }

    public function testFilesystem()
    {
        $this->assertEquals('public', $this->obj['filesystem']);

        $ret = $this->obj->setFilesystem('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['filesystem']);
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $this->obj->setMultiple(false);
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    /**
     * Provide property data for {@see FileProperty::validateMimetypes()}.
     *
     * @used-by self::testValidateMimetypes()
     * @return  array
     */
    public function provideDataForValidateMimetypes()
    {
        $paths = $this->getFileMapOfFixtures();

        return [
            'any MIME types, no value' => [
                'propertyValues'          => null,
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => null,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'any MIME types, text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => null,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'any MIME types, image file' => [
                'propertyValues'          => $paths['panda.png'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => null,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, no value' => [
                'propertyValues'          => null,
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, single text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, single image file' => [
                'propertyValues'          => $paths['panda.png'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, nonexistent file' => [
                'propertyValues'          => $paths['nonexistent.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['nonexistent.txt'].'] not found or MIME type unrecognizable',
                    ],
                ],
            ],
            'text/plain, l10n, text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => true,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, l10n, text + image file' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'],
                    'fr' => $paths['panda.png'],
                ],
                'propertyL10n'            => true,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, multiple, text files' => [
                'propertyValues'          => [
                    $paths['document.txt'],
                    $paths['todo.txt'],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => true,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, multiple, text + image file' => [
                'propertyValues'          => [
                    $paths['document.txt'],
                    $paths['panda.png'],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => true,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, l10n + multiple #1' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'].','.$paths['todo.txt'],
                    'fr' => [ $paths['stuff.txt'], $paths['draft.txt'] ],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'text/plain, l10n + multiple #2' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'].','.$paths['scream.wav'],
                    'fr' => [ $paths['stuff.txt'], $paths['cat.jpg'] ],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['scream.wav'].'] has unacceptable MIME type [audio/%s]',
                        'File ['.$paths['cat.jpg'].'] has unacceptable MIME type [image/%s]',
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide property data for {@see FileProperty::validateFilesizes()}.
     *
     * @used-by self::testValidateFilesizes()
     * @return  array
     */
    public function provideDataForValidateFilesizes()
    {
        $paths = $this->getFileMapOfFixtures();

        return [
            'any size, no value' => [
                'propertyValues'          => null,
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 0,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'any size, text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 0,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, no value' => [
                'propertyValues'          => null,
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, single text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, single image file' => [
                'propertyValues'          => $paths['panda.png'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, nonexistent file' => [
                'propertyValues'          => $paths['nonexistent.txt'],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['nonexistent.txt'].'] not found or size unknown',
                    ],
                ],
            ],
            'max 10kB, l10n, text file' => [
                'propertyValues'          => $paths['document.txt'],
                'propertyL10n'            => true,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, l10n, text + image file' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'],
                    'fr' => $paths['panda.png'],
                ],
                'propertyL10n'            => true,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, multiple, text files' => [
                'propertyValues'          => [
                    $paths['document.txt'],
                    $paths['todo.txt'],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => true,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, multiple, text + image file' => [
                'propertyValues'          => [
                    $paths['document.txt'],
                    $paths['panda.png'],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => true,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, l10n + multiple #1' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'].','.$paths['todo.txt'],
                    'fr' => [ $paths['stuff.txt'], $paths['draft.txt'] ],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => true,
                'assertValidationResults' => [],
            ],
            'max 10kB, l10n + multiple #2' => [
                'propertyValues'          => [
                    'en' => $paths['document.txt'].','.$paths['scream.wav'],
                    'fr' => [ $paths['stuff.txt'], $paths['panda.png'] ],
                ],
                'propertyL10n'            => false,
                'propertyMultiple'        => false,
                'maxFilesize'             => 10240,
                'assertValidationReturn'  => false,
                'assertValidationResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['scream.wav'].'] exceeds maximum file size [%s]',
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide property data for {@see ImageProperty::generateExtension()}.
     *
     * @used-by AbstractFilePropertyTestCase::testGenerateExtensionFromDataProvider()
     * @return  array
     */
    public function provideDataForGenerateExtension()
    {
        return [
            [ 'text/plain',  'txt' ],
            [ 'text/html',   null ],
            [ 'image/x-foo', null ],
        ];
    }
}
