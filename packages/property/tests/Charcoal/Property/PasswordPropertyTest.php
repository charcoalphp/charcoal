<?php

declare(strict_types=1);

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

    private \Charcoal\Property\PasswordProperty $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new PasswordProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }
    public function testType(): void
    {
        $this->assertEquals('password', $this->obj->type());
    }

    public function testSave(): void
    {
        $v1 = $this->obj->save('xxx');
        $this->assertNotEquals($v1, 'xxx');

        $v2 = $this->obj->save($v1);
        $this->assertEquals($v1, $v2);
    }
}
