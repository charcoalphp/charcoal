<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\View\AbstractLoader;

/**
 *
 */
class AbstractLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance of object under test
     * @var AbstractViewClass $obj
     */
    public $obj;

    /**
     *
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
     *
     */
    public function testInvalidBasePathThrowsException()
    {
        $this->setExpectedException('\Exception');

        $logger = new NullLogger();
        $loader = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'logger'    => $logger,
            'base_path' => false,
            'paths'     => ['Mustache/templates']
        ]]);
    }

    /**
     *
     */
    public function testPathsThrowsException()
    {
        $this->setExpectedException('\Exception');

        $logger = new NullLogger();
        $loader = $this->getMockForAbstractClass(AbstractLoader::class, [[
            'logger'    => $logger,
            'base_path' => __DIR__,
            'paths'     => [false]
        ]]);
    }

    public function testDynamicTemplateInvalidVarName()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->dynamicTemplate(null);
    }

    public function testSetDynamicTemplateInvalidVarName()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDynamicTemplate(null, 'foo');
    }

    public function testSetDynamicTemplate()
    {
        $this->assertNull($this->obj->setDynamicTemplate('dynamic', 'foo'));
        $this->assertEquals('foo', $this->obj->dynamicTemplate('dynamic'));
    }

    public function testSetDynamicTemplateInvalidTemplateIdent()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setDynamicTemplate('foo', []);
    }

    public function testRemoveDynamicTemplate()
    {
        $this->obj->setDynamicTemplate('foo', null);
        $this->obj->removeDynamicTemplate('foo');

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->removeDynamicTemplate(null);
    }

    public function testClearDynamicTemplate()
    {
        $this->obj->clearDynamicTemplates();
    }
}
