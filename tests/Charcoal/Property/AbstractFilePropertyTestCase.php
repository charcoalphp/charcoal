<?php

namespace Charcoal\Tests\Property;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\FileProperty;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\FixturesTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Property\ContainerIntegrationTrait;

/**
 * Test common file property features
 */
abstract class AbstractFilePropertyTestCase extends AbstractTestCase
{
    use ContainerIntegrationTrait;
    use FixturesTrait;
    use ReflectionsTrait;

    const FIXTURES = [
        'buzzer.mp3',
        'cat.jpg',
        'document.txt',
        'draft.txt',
        'nonexistent.txt',
        'blank.txt',
        'panda.png',
        'scream.wav',
        'stuff.txt',
        'todo.txt',
    ];

    /**
     * @var PropertyInterface
     */
    public $obj;

    /**
     * @var array<string, string>
     */
    private $fileMapOfFixtures;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->obj = $this->createProperty();
    }

    /**
     * @return array<string, string>
     */
    public function getFileMapOfFixtures()
    {
        if ($this->fileMapOfFixtures === null) {
            $this->fileMapOfFixtures = [];
            foreach (self::FIXTURES as $filename) {
                $this->fileMapOfFixtures[$filename] = $this->getPathToFixture('files/'.$filename);
            }
        }

        return $this->fileMapOfFixtures;
    }

    /**
     * Reports an error identified by $message if $validator does not have the $results.
     *
     * @param  array $expected The expected results.
     * @param  array $actual   The actual results.
     * @return void
     */
    public function assertValidatorHasResults($expected, $actual)
    {
        foreach ($actual as $level => $results) {
            $this->assertArrayHasKey(
                $level,
                $expected,
                sprintf(
                    'Failed asserting that validator results has the level \'%s\'.',
                    $level
                )
            );

            foreach ($results as $i => $result) {
                $this->assertArrayHasKey(
                    $i,
                    $expected[$level],
                    'Failed asserting that validator results contains an expected message.'
                );

                $this->assertStringMatchesFormat(
                    $expected[$level][$i],
                    $result->message()
                );
            }
        }
    }

    /**
     * Asserts that the property implements {@see FileProperty}.
     *
     * @coversNothing
     * @return void
     */
    public function testFilePropertyInterface()
    {
        $this->assertInstanceOf(FileProperty::class, $this->obj);
    }

    /**
     * Asserts that the property adheres to file property defaults.
     *
     * @return void
     */
    public function testPropertyDefaults()
    {
        $obj = $this->obj;

        $this->assertEquals(FileProperty::DEFAULT_UPLOAD_PATH, $obj['uploadPath']);
        $this->assertEquals(FileProperty::DEFAULT_FILESYSTEM, $obj['filesystem']);
        $this->assertEquals(FileProperty::DEFAULT_PUBLIC_ACCESS, $obj['publicAccess']);
        $this->assertEquals(FileProperty::DEFAULT_OVERWRITE, $obj['overwrite']);
        $this->assertEquals($obj['defaultAcceptedMimetypes'], $obj['acceptedMimetypes']);
        $this->assertEquals($obj->maxFilesizeAllowedByPhp(), $obj['maxFilesize']);
    }

    /**
     * Asserts that the file property will generate
     * the expected extension from a given dataset.
     *
     * @dataProvider provideDataForGenerateExtension
     *
     * @param  string $mime A MIME type.
     * @param  string $ext  The expected file extension.
     * @return void
     */
    public function testGenerateExtensionFromDataProvider($mime, $ext)
    {
        $this->obj['mimetype'] = $mime;
        $this->assertEquals($mime, $this->obj['mimetype']);
        $this->assertEquals($ext, $this->obj->generateExtension());
    }

    /**
     * Asserts that the file property will generate an extension
     * for all default accepted MIME types.
     *
     * @return void
     */
    public function testGenerateExtensionFromDefaultAcceptedMimeTypes()
    {
        $mimes = $this->obj['defaultAcceptedMimetypes'];
        if (empty($mimes)) {
            // PHPUnit 7+
            # $this->expectNotToPerformAssertions();
            // PHPUnit 5/6
            $this->assertTrue(true);
            return;
        }

        foreach ($mimes as $mime) {
            $this->obj['mimetype'] = $mime;
            $this->assertEquals($mime, $this->obj['mimetype']);

            $ext = $this->obj->generateExtension();
            $this->assertInternalType('string', $ext);
            $this->assertNotEmpty($ext);
        }
    }

    /**
     * Asserts that the uploadPath always ends with a trailing "/".
     *
     * @return void
     */
    public function testUploadPath()
    {
        $obj = $this->obj;

        $return = $obj->setUploadPath('storage/path/a');
        $this->assertSame($obj, $return);
        $this->assertEquals('storage/path/a/', $obj->getUploadPath());

        $obj['uploadPath'] = 'uploads/path/b///';
        $this->assertEquals('uploads/path/b/', $obj['uploadPath']);

        $this->expectException(InvalidArgumentException::class);
        $obj->setUploadPath(42);
    }

    /**
     * Asserts that the property can store a filesize.
     *
     * @covers \Charcoal\Property\FileProperty::setFilesize()
     * @covers \Charcoal\Property\FileProperty::getFilesize()
     * @return void
     */
    public function testFilesize()
    {
        $return = $this->obj->setFilesize(1024);
        $this->assertSame($this->obj, $return);
        $this->assertEquals(1024, $this->obj->getFilesize());

        $this->obj->setFilesize(null);
        $this->assertEquals(0, $this->obj->getFilesize());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setFilesize(false);
    }

    /**
     * Asserts that the property returns NULL if it can not resolve the filesize from its value.
     *
     * @covers \Charcoal\Property\FileProperty::getFilesize()
     * @return void
     */
    public function testFilesizeFromBadVal()
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/blank.txt');

        $this->assertEquals(0, $obj['filesize']);
    }

    /**
     * Asserts that the property can store a MIME type.
     *
     * @covers \Charcoal\Property\FileProperty::setMimetype()
     * @covers \Charcoal\Property\FileProperty::getMimetype()
     * @return void
     */
    public function testMimetype()
    {
        $return = $this->obj->setMimetype('foo');
        $this->assertSame($this->obj, $return);
        $this->assertEquals('foo', $this->obj->getMimetype());

        $this->obj->setMimetype(null);
        $this->assertNull($this->obj->getMimetype());

        $this->obj['mimetype'] = false;
        $this->assertNull($this->obj['mimetype']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMimetype([]);
    }

    /**
     * Asserts that the property returns NULL if it can not resolve the MIME type from its value.
     *
     * @covers \Charcoal\Property\FileProperty::getMimetype()
     * @return void
     */
    public function testMimetypeFromBadVal()
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/blank.txt');

        $this->assertNull($obj['mimetype']);
    }

    /**
     * Asserts that the property supports accepted MIME types.
     *
     * @covers \Charcoal\Property\FileProperty::setAcceptedMimetypes()
     * @covers \Charcoal\Property\FileProperty::getAcceptedMimetypes()
     * @return void
     */
    public function testAcceptedMimeTypes()
    {
        $obj = $this->obj;

        $return = $obj->setAcceptedMimetypes([ 'text/plain', 'text/csv', 'text/html' ]);
        $this->assertSame($obj, $return);

        $accpetedMimeTypes = $obj->getAcceptedMimetypes();
        $this->assertInternalType('array', $accpetedMimeTypes);
        $this->assertCount(3, $accpetedMimeTypes);
        $this->assertContains('text/plain', $accpetedMimeTypes);
        $this->assertContains('text/csv', $accpetedMimeTypes);
        $this->assertContains('text/html', $accpetedMimeTypes);

        $obj->setAcceptedMimetypes([ 'text/css', 0 ]);

        $accpetedMimeTypes = $obj->getAcceptedMimetypes();
        $this->assertCount(1, $accpetedMimeTypes);
        $this->assertContains('text/css', $accpetedMimeTypes);

        $obj->setAcceptedMimetypes([]);

        $accpetedMimeTypes = $obj->getAcceptedMimetypes();
        $this->assertEquals($obj->getDefaultAcceptedMimetypes(), $accpetedMimeTypes);

        $this->expectException(InvalidArgumentException::class);
        $obj->setAcceptedMimetypes('text/plain');
    }

    /**
     * Asserts that the property adheres to file property defaults.
     *
     * @covers \Charcoal\Property\FileProperty::getDefaultAcceptedMimetypes()
     * @return void
     */
    abstract public function testDefaulAcceptedMimeTypes();

    /**
     * Asserts that the property properly checks if
     * any acceptable MIME types are available.
     *
     * @covers \Charcoal\Property\FileProperty::hasAcceptedMimetypes()
     * @return void
     */
    abstract public function testHasAcceptedMimeTypes();

    /**
     * Asserts that the property can resolve a filesize from its value.
     *
     * @return void
     */
    abstract public function testFilesizeFromVal();

    /**
     * Asserts that the property can resolve a MIME type from its value.
     *
     * @return void
     */
    abstract public function testMimetypeFromVal();

    /**
     * Asserts that the `type()` method is "file".
     *
     * @covers \Charcoal\Property\FileProperty::type()
     * @return void
     */
    abstract public function testPropertyType();

    /**
     * Create a file property instance.
     *
     * @return PropertyInterface
     */
    abstract public function createProperty();

    /**
     * Provide property data for {@see FileProperty::generateExtension()}.
     *
     * @used-by self::testGenerateExtension()
     * @return  array Format: `[ "mime-type", "extension" ]`
     */
    abstract public function provideDataForGenerateExtension();
}
