<?php

namespace Charcoal\Tests\Property;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\ImageProperty;

#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\ImageProperty::class, 'type()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\ImageProperty::class, 'getDefaultAcceptedMimetypes()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Property\ImageProperty::class, 'hasAcceptedMimetypes()')]
class ImagePropertyTest extends AbstractFilePropertyTestCase
{
    /**
     * Create a file property instance.
     */
    public function createProperty(): \Charcoal\Property\ImageProperty
    {
        $container = $this->getContainer();

        return new ImageProperty([
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
        $this->assertEquals('image', $this->obj->type());
    }

    /**
     * Asserts that the property adheres to file property defaults.
     */
    #[\Override]
    public function testPropertyDefaults(): void
    {
        parent::testPropertyDefaults();

        $this->assertIsArray($this->obj['effects']);
        $this->assertEmpty($this->obj['effects']);

        $this->assertEquals(ImageProperty::DEFAULT_DRIVER_TYPE, $this->obj['driverType']);
        $this->assertEquals(ImageProperty::DEFAULT_APPLY_EFFECTS, $this->obj['applyEffects']);
    }

    /**
     * Asserts that the property adheres to file property defaults.
     */
    public function testDefaulAcceptedMimeTypes(): void
    {
        $this->assertIsArray($this->obj['defaultAcceptedMimetypes']);
        $this->assertNotEmpty($this->obj['defaultAcceptedMimetypes']);
    }

    /**
     * Asserts that the property properly checks if
     * any acceptable MIME types are available.
     */
    public function testHasAcceptedMimeTypes(): void
    {
        $this->assertTrue($this->obj->hasAcceptedMimetypes());

        $this->obj->setAcceptedMimetypes([ 'image/gif' ]);
        $this->assertTrue($this->obj->hasAcceptedMimetypes());
    }

    /**
     * Asserts that the property can resolve a filesize from its value.
     */
    public function testFilesizeFromVal(): void
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/panda.png');

        $this->assertEquals(170276, $obj['filesize']);
    }

    /**
     * Asserts that the property can resolve a MIME type from its value.
     */
    public function testMimetypeFromVal(): void
    {
        $obj = $this->obj;

        $obj['uploadPath'] = $this->getPathToFixtures().'/files';
        $obj['val'] = $this->getPathToFixture('files/panda.png');

        $this->assertEquals('image/png', $obj['mimetype']);
    }

    public function testSetEffects(): void
    {
        $this->assertEquals([], $this->obj['effects']);
        $ret = $this->obj->setEffects([['type'=>'blur', 'sigma'=>'1']]);
        $this->assertSame($ret, $this->obj);

        $this->obj['effects'] = [['type'=>'blur', 'sigma'=>'1'], ['type'=>'revert']];
        $this->assertEquals(2, count($this->obj['effects']));

        $this->obj->set('effects', [['type'=>'blur', 'sigma'=>'1']]);
        $this->assertEquals(1, count($this->obj['effects']));

        $this->assertEquals(1, count($this->obj['effects']));
    }

    public function testAddEffect(): void
    {
        $this->assertEquals(0, count($this->obj['effects']));

        $ret = $this->obj->addEffect(['type'=>'grayscale']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(1, count($this->obj['effects']));

        $this->obj->addEffect(['type'=>'blur', 'sigma'=>1]);
        $this->assertEquals(2, count($this->obj['effects']));
    }

    public function testSetApplyEffects(): void
    {
        $this->assertEquals('save', $this->obj['applyEffects']);
        $this->assertTrue($this->obj->canApplyEffects('save'));
        $this->assertFalse($this->obj->canApplyEffects('upload'));

        $ret = $this->obj->setApplyEffects('never');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('never', $this->obj['applyEffects']);
        $this->assertTrue($this->obj->canApplyEffects('never'));

        $this->obj->setApplyEffects('');
        $this->assertEquals('save', $this->obj['applyEffects']);

        $this->obj->setApplyEffects(null);
        $this->assertEquals('save', $this->obj['applyEffects']);

        $this->obj->setApplyEffects(false);
        $this->assertEquals('never', $this->obj['applyEffects']);

        $this->obj->setApplyEffects('upload');
        $this->assertEquals('upload', $this->obj['applyEffects']);
        $this->assertTrue($this->obj->canApplyEffects('upload'));

        $this->expectException(\OutOfBoundsException::class);
        $this->obj->setApplyEffects('foobar');
    }

    public function testDriverType(): void
    {
        $this->assertEquals(ImageProperty::DEFAULT_DRIVER_TYPE, $this->obj['driverType']);
        $ret = $this->obj->setDriverType('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['driverType']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDriverType(false);
    }

    public function testProcessEffects(): void
    {
        $ret = $this->obj->processEffects(null, []);
        $this->assertNull($ret);
    }

    #[\Override]
    public function testAcceptedMimetypes(): void
    {
        $ret = $this->obj['acceptedMimetypes'];
        $this->assertContains('image/png', $ret);
        $this->assertContains('image/jpg', $ret);
    }

    /**
     * Provide property data for {@see ImageProperty::generateExtension()}.
     *
     * @used-by AbstractFilePropertyTestCase::testGenerateExtensionFromDataProvider()
     */
    public static function provideDataForGenerateExtension(): array
    {
        return [
            [ 'image/gif',     'gif' ],
            [ 'image/jpg',     'jpg' ],
            [ 'image/jpeg',    'jpg' ],
            [ 'image/pjpeg',   'jpg' ],
            [ 'image/png',     'png' ],
            [ 'image/svg+xml', 'svg' ],
            [ 'image/webp',    'webp' ],
            [ 'image/x-foo',    null ],
            [ 'video/webm',     null ],
        ];
    }
}
