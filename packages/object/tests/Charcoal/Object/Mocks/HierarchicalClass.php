<?php

declare(strict_types=1);

namespace Charcoal\Tests\Object\Mocks;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalInterface;
use Charcoal\Object\HierarchicalTrait;
use Charcoal\Tests\Object\Mocks\AbstractModel;

/**
 *
 */
class HierarchicalClass extends AbstractModel implements
    HierarchicalInterface
{
    use HierarchicalTrait;

    public static function objType(): string
    {
        return 'charcoal/tests/object/hierarchical-class';
    }

    public function loadChildren(): array
    {
        return [];
    }
}
