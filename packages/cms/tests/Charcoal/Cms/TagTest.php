<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\Tag;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class TagTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\Tag $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new Tag($dependencies);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'name'       => 'Foo?',
            'color'      => 'Bar',
            'variations' => [
                'en' => 'a,b,c',
            ],
            'search_weight' => 42,
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo?', (string)$this->obj->name());
        $this->assertEquals('Bar', (string)$this->obj->color());
        $this->assertEquals('a,b,c', $this->obj->variations());
        $this->assertEquals(42, $this->obj->searchWeight());
    }

    public function testSetName(): void
    {
        $ret = $this->obj->setName('Foo?');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo?', $this->obj->name());
    }

    public function testSetColor(): void
    {
        $ret = $this->obj->setColor('Bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar', $this->obj->color());
    }

    public function testSetVariations(): void
    {
        $ret = $this->obj->setVariations('foo,bar,baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo,bar,baz', $this->obj->variations());
    }

    public function testSetSearchWeight(): void
    {
        $ret = $this->obj->setSearchWeight(1984);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(1984, $this->obj->searchWeight());
    }
}
