<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\PasswordProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PasswordPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var PasswordProperty
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new PasswordProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }
    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('password', $this->obj->type());
    }

    public function testSave()
    {
        $v1 = $this->obj->save('xxx');
        $this->assertNotEquals($v1, 'xxx');

        $v2 = $this->obj->save($v1);
        $this->assertEquals($v1, $v2);
    }
}
