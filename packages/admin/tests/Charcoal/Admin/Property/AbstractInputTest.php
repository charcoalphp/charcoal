<?php

namespace Charcoal\Tests\Admin\Property;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class AbstractInputTest extends AbstractTestCase
{
    /**
     * @var AbstractPropertyInput
     */
    public $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = new Class([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader'],
        ]) extends AbstractPropertyInput {
            public function type(): string
            {
                return 'foo';
            }
        };
    }

    public function testSetData(): void
    {
        $obj = $this->obj;
        $ret = $obj->setData([
            'ident'=>'foo',
            'required'=>true,
            'disabled'=>true,
            'read_only'=>true
        ]);
        $this->assertSame($ret, $obj);
        $this->assertEquals('foo', $obj->ident());
        $this->assertTrue($obj->required());
        $this->assertTrue($obj->disabled());
        $this->assertTrue($obj->readOnly());
    }

    /**
     * Set up the service container.
     */
    protected function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerInputDependencies($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
