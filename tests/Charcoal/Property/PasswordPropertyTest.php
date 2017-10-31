<?php

namespace Charcoal\Tests\Property;

use Charcoal\Property\PasswordProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class PasswordPropertyTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

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
