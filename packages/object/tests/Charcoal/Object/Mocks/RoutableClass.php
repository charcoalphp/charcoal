<?php

declare(strict_types=1);

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\RoutableInterface;
use Charcoal\Object\RoutableTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class RoutableClass extends AbstractModel implements
    RoutableInterface
{
    use RoutableTrait;

    public static function objType(): string
    {
        return 'charcoal/tests/object/routable-class';
    }

    public function templateIdent(): null
    {
        return null;
    }
}
