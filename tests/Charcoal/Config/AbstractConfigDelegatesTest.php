<?php

namespace Charcoal\Tests\Config;

use PHPUnit_Framework_TestCase;

use Exception;
use InvalidArgumentException;

use Charcoal\Config\AbstractConfig;

/**
 * Test the separator functionalities of AbstractConfig.
 */
class AbstractConfigDelegatesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var mixed The Abstract Config mock
     */
    public $obj;

    public function setUp()
    {
        include_once 'AbstractEntityClass.php';
        $this->obj = $this->getMockForAbstractClass(AbstractConfig::class);
    }

    public function testConstructorDelegates()
    {
        $config = $this->obj;
        $config['foo'] = 42;
        $config['test'] = 'baz';
        $obj = $this->getMockForAbstractClass(AbstractConfig::class, [['foo'=>666], [$config]]);
        $this->assertEquals(666, $obj['foo']);
        $this->assertEquals('baz', $obj['test']);
    }

    /**
     * Asserts that
     * - The delegate is actually used when accessing a non-existing key.
     * - The order of the delegates are respected.
     */
    public function testDelegates()
    {
        $obj = $this->obj;

        $this->assertFalse($obj->has('foo'));

        $delegate = $this->getMockForAbstractClass(AbstractConfig::class);
        $delegate->set('foo', 'delegate1');
        $obj->addDelegate($delegate);

        $this->assertTrue($obj->has('foo'));
        $this->assertEquals('delegate1', $obj->get('foo'));

        $delegate2 = $this->getMockForAbstractClass(AbstractConfig::class);
        $delegate2->set('foo', 'delegate2');

        $obj->addDelegate($delegate2);
        $this->assertEquals('delegate1', $obj->get('foo'));

        $obj->prependDelegate($delegate2);
        $this->assertEquals('delegate2', $obj->get('foo'));
    }
}
