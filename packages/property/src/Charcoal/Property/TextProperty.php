<?php

declare(strict_types=1);

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Text Property. Longer strings.
 */
class TextProperty extends StringProperty
{
    public const DEFAULT_LONG = false;

    /**
     * @var boolean
     */
    protected $long = self::DEFAULT_LONG;

    #[\Override]
    public function type(): string
    {
        return 'text';
    }

    /**
     * @param boolean $long Whether long text are supported.
     */
    public function setLong($long): static
    {
        $this->long = (bool) $long;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * String's default max length is overridden for the text property.
     * (0 = no max length).
     *
     * @see StringProperty::defaultMaxLength()
     */
    #[\Override]
    public function defaultMaxLength(): int
    {
        return 0;
    }

    /**
     * Get the SQL type (Storage format)
     *
     * @see StorablePropertyTrait::sqlType()
     * @return string The SQL type
     */
    #[\Override]
    public function sqlType(): string
    {
        if ($this['long'] === true) {
            return 'LONGTEXT';
        } else {
            return 'TEXT';
        }
    }
}
