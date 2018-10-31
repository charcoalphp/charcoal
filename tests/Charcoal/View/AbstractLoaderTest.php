<?php

namespace Charcoal\Tests\View;

use Exception;
use InvalidArgumentException;

// From PSR-3
use Psr\Log\NullLogger;

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
    public function setUp()
    {
        $logger = new NullLogger();
        $this->obj = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => ['Mustache/templates']
        ]]);
    }

    /**
     * @return void
     */
    public function testInvalidBasePathThrowsException()
    {
        $this->expectException(Exception::class);

        $logger = new NullLogger();
        $loader = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'logger'    => $logger,
            'base_path' => false,
            'paths'     => ['Mustache/templates']
        ]]);
    }

    /**
     * @return void
     */
    public function testPathsThrowsException()
    {
        $this->expectException('\Exception');

        $logger = new NullLogger();
        $loader = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => [false]
        ]]);
    }

    /**
     * @return void
     */
    public function testDynamicTemplateInvalidVarName()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->dynamicTemplate(null);
    }

    /**
     * @return void
     */
    public function testSetDynamicTemplateInvalidVarName()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->setDynamicTemplate(null, 'foo');
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
    public function testSetDynamicTemplateInvalidTemplateIdent()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setDynamicTemplate('foo', []);
    }

    /**
     * @return void
     */
    public function testRemoveDynamicTemplate()
    {
        $this->obj->setDynamicTemplate('foo', null);
        $this->obj->removeDynamicTemplate('foo');

        $this->expectException(InvalidArgumentException::class);
        $this->obj->removeDynamicTemplate(null);
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
