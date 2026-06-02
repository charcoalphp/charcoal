<?php

namespace Charcoal\Tests\View;

use Exception;
use InvalidArgumentException;


// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractLoaderTest extends AbstractTestCase
{
    /**
     * Instance of object under test
     * @var AbstractViewClass $obj
     */
    public $obj;

    public function setUp(): void
    {
        $this->obj = new class ([
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]) extends AbstractLoader {
            protected function filenameFromIdent(string $ident): string { }
        };
    }

    public function testGetDynamicTemplateReturnsEmptyTemplateForUndefinedVarName(): void
    {
        $this->assertEquals('', $this->obj->dynamicTemplate('foo'));
    }

    public function testSetDynamicTemplate(): void
    {
        $this->assertNull($this->obj->setDynamicTemplate('dynamic', 'foo'));
        $this->assertEquals('foo', $this->obj->dynamicTemplate('dynamic'));
    }

    public function testClearDynamicTemplate(): void
    {
        $this->obj->clearDynamicTemplates();
        $this->assertInstanceOf(AbstractLoader::class, $this->obj);
    }
}
