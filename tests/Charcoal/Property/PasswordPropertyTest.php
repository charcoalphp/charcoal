<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\PasswordProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class PasswordPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @return void
     */
    public function testType()
    {
        $container = $this->getContainer();

        $obj = new PasswordProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
        $this->assertEquals('password', $obj->type());
    }
}
