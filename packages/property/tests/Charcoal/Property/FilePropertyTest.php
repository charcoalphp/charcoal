<?php

namespace Charcoal\Tests\Property;

use PDO;
use InvalidArgumentException;
use ReflectionClass;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorInterface as Validator;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;

#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\FileProperty::class, 'type()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\FileProperty::class, 'getDefaultAcceptedMimetypes()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\FileProperty::class, 'hasAcceptedMimetypes()')]
class FilePropertyTest extends AbstractFilePropertyTestCase
{
    /**
     * Create a file property instance.
     */
    public function createProperty(): \Charcoal\Property\FileProperty
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
     */
    public function testPropertyType(): void
    {
        $this->assertEquals('file', $this->obj->type());
    }

    /**
     * Asserts that the property adheres to file property defaults.
     */
    public function testDefaulAcceptedMimeTypes(): void
    {
        $this->assertIsArray($this->obj['defaultAcceptedMimetypes']);
        $this->assertEmpty($this->obj['defaultAcceptedMimetypes']);
    }

    /**
     * Asserts that the property properly checks if
     * any acceptable MIME types are available.
     */
    public function testHasAcceptedMimeTypes(): void
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
     */
    public function testFilesizeFromVal(): void
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/document.txt');

        $this->assertEquals(743, $obj['filesize']);
    }

    /**
     * Asserts that the property can resolve a MIME type from its value.
     */
    public function testMimetypeFromVal(): void
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/document.txt');

        $this->assertEquals('text/plain', $obj['mimetype']);
    }

    public function testSetData(): void
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

    public function testSetOverwrite(): void
    {
        $ret = $this->obj->setOverwrite(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['overwrite']);

        $this->obj['overwrite'] = false;
        $this->assertFalse($this->obj['overwrite']);

        $this->obj->set('overwrite', true);
        $this->assertTrue($this->obj['overwrite']);
    }

    public function testVaidationMethods(): void
    {
        $methods = $this->obj->validationMethods();
        $this->assertContains('mimetypes', $methods);
        $this->assertContains('filesizes', $methods);
    }

    /**
     * Test validation file MIME types on property.
     *
     *
     * @param  mixed   $val               The value(s) to be validated.
     * @param  boolean $l10n              Whether the property value is multilingual.
     * @param  boolean $multiple          Whether the property accepts zero or more values.
     * @param  mixed   $acceptedMimetypes The accepted MIME types.
     * @param  boolean $expectedReturn    The expected return value of the method.
     * @param  array   $expectedResults   The expected validation results.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataForValidateMimetypes')]
    public function testValidateMimetypes(
        string|array|null $val,
        bool $l10n,
        bool $multiple,
        ?array $acceptedMimetypes,
        bool $expectedReturn,
        array $expectedResults = []
    ): void {
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
     *
     * @param  mixed   $val             The value(s) to be validated.
     * @param  boolean $l10n            Whether the property value is multilingual.
     * @param  boolean $multiple        Whether the property accepts zero or more values.
     * @param  integer $maxFilesize     The maximum file size accepted.
     * @param  boolean $expectedReturn  The expected return value of the method.
     * @param  array   $expectedResults The expected validation results.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataForValidateFilesizes')]
    public function testValidateFilesizes(
        string|array|null $val,
        bool $l10n,
        bool $multiple,
        int $maxFilesize,
        bool $expectedReturn,
        array $expectedResults = []
    ): void {
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

    public function testFileExists(): void
    {
        $obj = $this->obj;
        $this->assertTrue($obj->fileExists(__FILE__));

        // $this->assertTrue($obj->fileExists(strtolower(__FILE__), true));
        // $this->assertTrue($obj->fileExists(strtoupper(__FILE__), true));

        $this->assertFalse($obj->fileExists('foobar/baz/42'));
    }

    /**
     *
     * @param  string $path     A path to test.
     * @param  string $expected Whether the path is absolute (TRUE) or relative (FALSE).
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providePathsForIsAbsolutePath')]
    public function testIsAbsolutePath(?string $path, bool $expected): void
    {
        $result = $this->callMethodWith($this->obj, 'isAbsolutePath', $path);
        $this->assertEquals($expected, $result);
    }

    public static function providePathsForIsAbsolutePath(): array
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
     *
     * @param  string $filename  A dirty filename.
     * @param  string $sanitized A clean version of $filename.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('filenameProvider')]
    public function testSanitizeFilename(string $filename, string $sanitized): void
    {
        $obj = $this->obj;
        $this->assertEquals($sanitized, $obj->sanitizeFilename($filename));
    }

    public static function filenameProvider(): array
    {
        return [
            [ 'foobar',              'foobar'              ],
            [ '<foo/bar*baz?x:y|z>', '_foo_bar_baz_x_y_z_' ],
            [ '.htaccess',           'htaccess'            ],
            [ '../../etc/passwd',    '_.._etc_passwd'      ],
        ];
    }

    public function testGenerateFilename(): void
    {
        $obj = $this->obj;
        $obj->setIdent('foo');
        $ret = $obj->generateFilename();
        $this->assertStringContainsString('Foo', $ret);
        $this->assertStringContainsString(date('Y-m-d'), $ret);

        $obj->setLabel('foobar');
        $ret = $obj->generateFilename();
        $this->assertStringContainsString('foobar', $ret);
    }

    public function testGenerateUniqueFilename(): void
    {
        $ret = $this->obj->generateUniqueFilename('foo.png');
        $this->assertStringContainsString('foo', $ret);
        $this->assertStringEndsWith('.png', $ret);
        $this->assertNotEquals($ret, 'foo');
    }

    public function testFilesystem(): void
    {
        $this->assertEquals('public', $this->obj['filesystem']);

        $ret = $this->obj->setFilesystem('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['filesystem']);
    }

    public function testSqlExtra(): void
    {
        $this->assertEquals('', $this->obj->sqlExtra());
    }

    public function testSqlType(): void
    {
        $this->obj->setMultiple(false);
        $this->assertEquals('VARCHAR(255)', $this->obj->sqlType());

        $this->obj->setMultiple(true);
        $this->assertEquals('TEXT', $this->obj->sqlType());
    }

    public function testSqlPdoType(): void
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }

    /**
     * Provide property data for {@see FileProperty::validateMimetypes()}.
     *
     * @used-by self::testValidateMimetypes()
     */
    public function provideDataForValidateMimetypes(): array
    {
        $paths = $this->getFileMapOfFixtures();

        return [
            'any MIME types, no value' => [
                'val'          => null,
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => null,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'any MIME types, text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => null,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'any MIME types, image file' => [
                'val'          => $paths['panda.png'],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => null,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, no value' => [
                'val'          => null,
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, single text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, single image file' => [
                'val'          => $paths['panda.png'],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, nonexistent file' => [
                'val'          => $paths['nonexistent.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['nonexistent.txt'].'] not found or MIME type unrecognizable',
                    ],
                ],
            ],
            'text/plain, l10n, text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => true,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, l10n, text + image file' => [
                'val'          => [
                    'en' => $paths['document.txt'],
                    'fr' => $paths['panda.png'],
                ],
                'l10n'            => true,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, multiple, text files' => [
                'val'          => [
                    $paths['document.txt'],
                    $paths['todo.txt'],
                ],
                'l10n'            => false,
                'multiple'        => true,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, multiple, text + image file' => [
                'val'          => [
                    $paths['document.txt'],
                    $paths['panda.png'],
                ],
                'l10n'            => false,
                'multiple'        => true,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] has unacceptable MIME type [image/png]',
                    ],
                ],
            ],
            'text/plain, l10n + multiple #1' => [
                'val'          => [
                    'en' => $paths['document.txt'].','.$paths['todo.txt'],
                    'fr' => [ $paths['stuff.txt'], $paths['draft.txt'] ],
                ],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'text/plain, l10n + multiple #2' => [
                'val'          => [
                    'en' => $paths['document.txt'].','.$paths['scream.wav'],
                    'fr' => [ $paths['stuff.txt'], $paths['cat.jpg'] ],
                ],
                'l10n'            => false,
                'multiple'        => false,
                'acceptedMimetypes'       => [ 'text/plain' ],
                'expectedReturn'  => false,
                'expectedResults' => [
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
     */
    public function provideDataForValidateFilesizes(): array
    {
        $paths = $this->getFileMapOfFixtures();

        return [
            'any size, no value' => [
                'val'          => null,
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 0,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'any size, text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 0,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, no value' => [
                'val'          => null,
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, single text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, single image file' => [
                'val'          => $paths['panda.png'],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, nonexistent file' => [
                'val'          => $paths['nonexistent.txt'],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['nonexistent.txt'].'] not found or size unknown',
                    ],
                ],
            ],
            'max 10kB, l10n, text file' => [
                'val'          => $paths['document.txt'],
                'l10n'            => true,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, l10n, text + image file' => [
                'val'          => [
                    'en' => $paths['document.txt'],
                    'fr' => $paths['panda.png'],
                ],
                'l10n'            => true,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, multiple, text files' => [
                'val'          => [
                    $paths['document.txt'],
                    $paths['todo.txt'],
                ],
                'l10n'            => false,
                'multiple'        => true,
                'maxFilesize'             => 10240,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, multiple, text + image file' => [
                'val'          => [
                    $paths['document.txt'],
                    $paths['panda.png'],
                ],
                'l10n'            => false,
                'multiple'        => true,
                'maxFilesize'             => 10240,
                'expectedReturn'  => false,
                'expectedResults' => [
                    Validator::ERROR => [
                        'File ['.$paths['panda.png'].'] exceeds maximum file size [%s]',
                    ],
                ],
            ],
            'max 10kB, l10n + multiple #1' => [
                'val'          => [
                    'en' => $paths['document.txt'].','.$paths['todo.txt'],
                    'fr' => [ $paths['stuff.txt'], $paths['draft.txt'] ],
                ],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => true,
                'expectedResults' => [],
            ],
            'max 10kB, l10n + multiple #2' => [
                'val'          => [
                    'en' => $paths['document.txt'].','.$paths['scream.wav'],
                    'fr' => [ $paths['stuff.txt'], $paths['panda.png'] ],
                ],
                'l10n'            => false,
                'multiple'        => false,
                'maxFilesize'             => 10240,
                'expectedReturn'  => false,
                'expectedResults' => [
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
     */
    public static function provideDataForGenerateExtension(): array
    {
        return [
            [ 'text/plain',  'txt' ],
            [ 'text/html',   null ],
            [ 'image/x-foo', null ],
        ];
    }
}
