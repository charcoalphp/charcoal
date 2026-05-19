<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Display;

use InvalidArgumentException;
use Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Hierarchical Indented Display Property
 */
class HierarchicalDisplay extends AbstractPropertyDisplay
{
    /**
     * Current level for output (of the object).
     */
    private int $currentLevel = 1;

    /**
     * The indentation symbol for output.
     */
    private string $indentation = '─ ';

    #[\Override]
    public function displayType(): string
    {
        return 'charcoal/admin/property/display/text';
    }

    #[\Override]
    public function displayVal(): string
    {
        $prop  = $this->p();
        $pad   = str_repeat($this->indentation(), ($this->currentLevel() - 1));
        $value = $prop->displayVal($this->propertyVal());

        return $pad . $value;
    }

    /**
     * Set the string to use for displaying the current level.
     *
     * @param string $indent A string to mark nested objects.
     * @throws InvalidArgumentException If the indentation is not a string.
     * @return AbstractConfig Chainable
     */
    public function setIndentation($indent): static
    {
        if (!is_string($indent)) {
            throw new InvalidArgumentException(
                'Indentation needs to be a string.'
            );
        }

        $this->indentation = $indent;

        return $this;
    }

    /**
     * Retrieve the indentation string.
     */
    public function indentation(): string
    {
        return $this->indentation;
    }

    /**
     * Set the current level for output of the associated object.
     *
     * Starts at "1" (top-level).
     *
     * @param  integer $level The level of depth.
     * @throws InvalidArgumentException If the level is not an integer.
     */
    public function setCurrentLevel($level): static
    {
        if (!is_int($level)) {
            throw new InvalidArgumentException(
                'The level must be an integer.'
            );
        }

        $this->currentLevel = $level;

        return $this;
    }

    /**
     * Retrieve the current level for output of the associated object.
     */
    public function currentLevel(): int
    {
        return $this->currentLevel;
    }
}
