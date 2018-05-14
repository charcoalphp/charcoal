<?php

namespace Charcoal\Tests\Translator;

use InvalidArgumentException;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorConfig;

/**
 *
 */
class TranslatorConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var TranslatorConfig
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $this->obj = new TranslatorConfig();
    }

    public function testDefaultsArrayAccess()
    {
        $this->assertEquals([ 'csv' ], $this->obj['loaders']);
        $this->assertContains('translations/', $this->obj['paths']);
        $this->assertFalse($this->obj['debug']);
        $this->assertEquals('../cache/translator', $this->obj['cache_dir']);
    }

    public function testSetLoaders()
    {
        $this->assertEquals([ 'csv' ], $this->obj->loaders());

        $ret = $this->obj->setLoaders([ 'csv', 'xliff' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'csv', 'xliff' ], $this->obj->loaders());

        $this->obj['loaders'] = [ 'php' ];
        $this->assertEquals([ 'php' ], $this->obj['loaders']);
    }

    public function testSetUnavailableLoaders()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj['loaders'] = [ 'foo' ];
    }

    public function testSetInvalidPaths()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj['paths'] = [ false ];
    }

    public function testSetInvalidDomainTranslations()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj['translations'] = [ false ];
    }

    public function testSetInvalidMessageTranslations()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj['translations'] = [ [ false ] ];
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
        $this->assertEquals('../cache/translator', $this->obj->cacheDir());
        $ret = $this->obj->setCacheDir('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->cacheDir());

        $this->obj['cache_dir'] = 'bar';
        $this->assertEquals('bar', $this->obj['cache_dir']);
    }

    public function testSetInvalidCacheDir()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->obj['cache_dir'] = false;
    }
}
