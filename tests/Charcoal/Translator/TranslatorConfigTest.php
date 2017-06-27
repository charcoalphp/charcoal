<?php

namespace Charcoal\Tests\Translator;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From `charcoal-translator`
use Charcoal\Translator\TranslatorConfig;

/**
 *
 */
class TranslatorConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Object under test.
     * @var TranslatorConfig
     */
    private $obj;

    public function setUp()
    {
        $this->obj = new TranslatorConfig();
    }

    public function testDefaultsArrayAccess()
    {
        $this->assertEquals(['csv'], $this->obj['loaders']);
        $this->assertContains('translations/', $this->obj['paths']);
        $this->assertFalse($this->obj['debug']);
        $this->assertEquals('translator_cache', $this->obj['cache_dir']);
    }

    public function testSetLoaders()
    {
        $this->assertEquals(['csv'], $this->obj->loaders());

        $ret = $this->obj->setLoaders(['csv','xliff']);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['csv', 'xliff'], $this->obj->loaders());

        $this->obj['loaders'] = ['php'];
        $this->assertEquals(['php'], $this->obj['loaders']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj['loaders'] = ['foo'];
    }

    public function testSetPaths()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj['paths'] = [false];
    }

    public function testSetDebug()
    {
        $this->assertFalse($this->obj->debug());
        $ret = $this->obj->setDebug(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->debug());

        $this->obj['debug'] = 0;
        $this->assertFalse($this->obj['debug']);
    }

    public function testSetCacheDir()
    {
        $this->assertEquals('translator_cache', $this->obj->cacheDir());
        $ret = $this->obj->setCacheDir('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->cacheDir());

        $this->obj['cache_dir'] = 'bar';
        $this->assertEquals('bar', $this->obj['cache_dir']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj['cache_dir'] = false;
    }
}
