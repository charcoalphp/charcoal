<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\AudioProperty;

/**
 *
 */
class AudioPropertyTest extends AbstractFilePropertyTestCase
{
    /**
     * Create a file property instance.
     *
     * @return AudioProperty
     */
    public function createProperty()
    {
        $container = $this->getContainer();

        return new AudioProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator'],
            'container'  => $container,
        ]);
    }

    /**
     * Asserts that the `type()` method is "file".
     *
     * @covers \Charcoal\Property\AudioProperty::type()
     * @return void
     */
    public function testPropertyType()
    {
        $this->assertEquals('audio', $this->obj->type());
    }

    /**
     * Asserts that the property adheres to file property defaults.
     *
     * @return void
     */
    public function testPropertyDefaults()
    {
        parent::testPropertyDefaults();

        $this->assertEquals(0, $this->obj['minLength']);
        $this->assertEquals(0, $this->obj['maxLength']);
    }

    /**
     * Asserts that the property adheres to file property defaults.
     *
     * @covers \Charcoal\Property\AudioProperty::getDefaultAcceptedMimetypes()
     * @return void
     */
    public function testDefaulAcceptedMimeTypes()
    {
        $this->assertInternalType('array', $this->obj['defaultAcceptedMimetypes']);
        $this->assertNotEmpty($this->obj['defaultAcceptedMimetypes']);
    }

    /**
     * Asserts that the property properly checks if
     * any acceptable MIME types are available.
     *
     * @covers \Charcoal\Property\AudioProperty::hasAcceptedMimetypes()
     * @return void
     */
    public function testHasAcceptedMimeTypes()
    {
        $this->assertTrue($this->obj->hasAcceptedMimetypes());

        $this->obj->setAcceptedMimetypes([ 'audio/wav' ]);
        $this->assertTrue($this->obj->hasAcceptedMimetypes());
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
        $obj['val'] = $this->getPathToFixture('files/buzzer.mp3');

        $this->assertEquals(16512, $obj['filesize']);
    }

    /**
     * Asserts that the property can resolve a MIME type from its value.
     *
     * Ignore issues under PHP 7.0 and PHP 7.1, see https://bugs.php.net/bug.php?id=78183
     *
     * @return void
     */
    public function testMimetypeFromVal()
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/buzzer.mp3');

        $mime = $obj['mimetype'];
        if ($mime === 'application/octet-stream') {
            $this->markTestSkipped(
                'Failed detecting MIME type for \'buzzer.mp3\'; received \'application/octet-stream\'.'
            );
        } else {
            $this->assertThat($obj['mimetype'], $this->logicalOr(
                $this->equalTo('audio/mp3'),
                $this->equalTo('audio/mpeg')
            ));
        }
    }

    /**
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $data = [
            'minLength' => 20,
            'maxLength' => 500,
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);

        $this->assertEquals(20, $obj['minLength']);
        $this->assertEquals(500, $obj['maxLength']);
    }

    /**
     * @return void
     */
    public function testSetDataSnakecase()
    {
        $obj = $this->obj;
        $data = [
          'min_length' => 20,
          'max_length' => 500,
        ];
        $ret = $obj->setData($data);
        $this->assertSame($ret, $obj);

        $this->assertEquals(20, $obj['minLength']);
        $this->assertEquals(500, $obj['maxLength']);
    }

    /**
     * @return void
     */
    public function testSetMinLength()
    {
        $ret = $this->obj->setMinLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj['minLength']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setMinLength(false);
    }

    /**
     * @return void
     */
    public function testSetMaxLength()
    {
        $ret = $this->obj->setMaxLength(5);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals(5, $this->obj['maxLength']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setMaxLength(false);
    }

    /**
     * @return void
     */
    public function testAcceptedMimetypes()
    {
        $ret = $this->obj['acceptedMimetypes'];
        $this->assertContains('audio/mp3', $ret);
        $this->assertContains('audio/mpeg', $ret);
        $this->assertContains('audio/wav', $ret);
        $this->assertContains('audio/x-wav', $ret);
    }

    /**
     * Provide property data for {@see AudioProperty::generateExtension()}.
     *
     * @used-by AbstractFilePropertyTestCase::testGenerateExtensionFromDataProvider()
     * @return  array
     */
    public function provideDataForGenerateExtension()
    {
        return [
            [ 'audio/mp3',      'mp3' ],
            [ 'audio/mpeg',     'mp3' ],
            [ 'audio/ogg',      'ogg' ],
            [ 'audio/webm',     'webm' ],
            [ 'audio/wav',      'wav' ],
            [ 'audio/wave',     'wav' ],
            [ 'audio/x-wav',    'wav' ],
            [ 'audio/x-pn-wav', 'wav' ],
            [ 'audio/x-foo',    null ],
            [ 'video/webm',     null ],
        ];
    }
}
