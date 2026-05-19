<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorConfig;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class TranslatorConfigTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Translator\TranslatorConfig|array $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new TranslatorConfig();
    }

    public function testDefaultsArrayAccess(): void
    {
        $this->assertEquals([ 'csv' ], $this->obj['loaders']);
        $this->assertContains('translations/', $this->obj['paths']);
        $this->assertFalse($this->obj['debug']);
        $this->assertEquals('../cache/translator', $this->obj['cache_dir']);
    }

    public function testSetLoaders(): void
    {
        $this->assertEquals([ 'csv' ], $this->obj->loaders());

        $ret = $this->obj->setLoaders([ 'csv', 'xliff' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'csv', 'xliff' ], $this->obj->loaders());

        $this->obj['loaders'] = [ 'php' ];
        $this->assertEquals([ 'php' ], $this->obj['loaders']);
    }

    public function testSetUnavailableLoaders(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj['loaders'] = [ 'foo' ];
    }

    public function testSetInvalidPaths(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj['paths'] = [ false ];
    }

    public function testSetInvalidDomainTranslations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj['translations'] = [ false ];
    }

    public function testSetInvalidMessageTranslations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj['translations'] = [ [ false ] ];
    }

    public function testSetDebug(): void
    {
        $this->assertFalse($this->obj->debug());
        $ret = $this->obj->setDebug(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->debug());

        $this->obj['debug'] = 0;
        $this->assertFalse($this->obj['debug']);
    }

    public function testSetCacheDir(): void
    {
        $this->assertEquals('../cache/translator', $this->obj->cacheDir());
        $ret = $this->obj->setCacheDir('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->cacheDir());

        $this->obj['cache_dir'] = 'bar';
        $this->assertEquals('bar', $this->obj['cache_dir']);
    }

    public function testSetInvalidCacheDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj['cache_dir'] = false;
    }
}
