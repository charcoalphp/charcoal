<?php

declare(strict_types=1);

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\PublishableInterface;
use Charcoal\Object\PublishableTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class PublishableClass extends AbstractModel implements
    PublishableInterface
{
    use PublishableTrait;

    public static function objType(): string
    {
        return 'charcoal/tests/object/publishable-class';
    }
}
