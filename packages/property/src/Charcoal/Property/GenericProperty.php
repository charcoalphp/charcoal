<?php

declare(strict_types=1);

namespace Charcoal\Property;

use PDO;
// From 'charcoal-property'
use Charcoal\Property\AbstractProperty;

/**
 * The most basic (generic) property possible, from abstract.
 */
class GenericProperty extends AbstractProperty
{
    public function type(): string
    {
        return 'generic';
    }

    /**
     * @see StorablePropertyTrait::sqlType()
     */
    public function sqlType(): string
    {
        if ($this['multiple']) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
    }

    /**
     * @see StorablePropertyTrait::sqlPdoType()
     */
    public function sqlPdoType(): int
    {
        return PDO::PARAM_STR;
    }
}
