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

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->obj = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'base_path' => __DIR__,
            'paths'     => [ 'Mustache/templates' ],
        ]]);
    }

    /**
     * @return void
     */
    public function testGetDynamicTemplateReturnsEmptyTemplateForUndefinedVarName()
    {
        $this->assertEquals('', $this->obj->dynamicTemplate('foo'));
    }

    /**
     * @return void
     */
    public function testSetDynamicTemplate()
    {
        $this->assertNull($this->obj->setDynamicTemplate('dynamic', 'foo'));
        $this->assertEquals('foo', $this->obj->dynamicTemplate('dynamic'));
    }

    /**
     * @return void
     */
    public function testClearDynamicTemplate()
    {
        $this->obj->clearDynamicTemplates();
        $this->assertInstanceOf(AbstractLoader::class, $this->obj);
    }
}
