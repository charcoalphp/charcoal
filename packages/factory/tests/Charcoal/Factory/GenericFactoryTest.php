<?php

namespace Charcoal\Tests\Factory;

use Charcoal\Factory\GenericFactory;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class GenericFactoryTest extends AbstractTestCase
{
    /**
     * @var GenericFactory
     */
    public $obj;

    protected function setUp(): void
    {
        $this->obj = new GenericFactory();
    }

    public function testIsResolvable(): void
    {
        $this->assertTrue($this->obj->isResolvable('DateTime'));
        $this->assertFalse($this->obj->isResolvable('foobaz'));

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->isResolvable(false);
    }

    public function testCreate(): void
    {
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->expectException(\Exception::class);
        $this->obj->create('foobar');
    }

    /**
     * Asserts that the AbstractFactory's `create()` method, as GenericFactory:
     * - Returns the default class when passing an invalid argument, if set
     * - Throws an exception when passing an invalid argument, if no default class is set
     */
    public function testCreateDefaultClass(): void
    {
        $this->obj->setDefaultClass('\DateTime');
        $ret = $this->obj->create('foobar');
        $this->assertInstanceOf('\DateTime', $ret);
    }

    public function testCreateCreatesNewInstance(): void
    {
        $ret1 = $this->obj->create('\DateTime');
        $ret2 = $this->obj->create('\DateTime');

        $this->assertNotSame($ret1, $ret2);
    }

    public function testCreateCallback(): void
    {
        $this->obj->create('\DateTime', null, function($obj): void {
            $this->assertInstanceOf('\DateTime', $obj);
        });
    }

    public function testGetReturnsSameInstance(): void
    {
        $ret1 = $this->obj->get('\DateTime');
        $ret2 = $this->obj->get('\DateTime');

        $this->assertSame($ret1, $ret2);
    }

    public function testCreateBaseClass(): void
    {
        $this->obj->setBaseClass('\DateTimeInterface');
        $ret = $this->obj->create('\DateTime');
        $this->assertInstanceOf('\DateTime', $ret);

        $this->expectException(\Exception::class);
        $this->obj->setBaseClass(\Charcoal\Factory\FactoryInterface::class);
        $this->obj->create('\DateTime');
    }
}
