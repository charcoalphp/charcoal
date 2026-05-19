<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;

/**
 * Range Property Input Type
 */
class RangeInput extends NumberInput
{
    /**
     * Whether to display the range value.
     */
    private bool $showRangeValue = false;

    /**
     * Where to display the range value ("prefix", "suffix").
     *
     * @var string|null
     */
    private $rangeValueLocation;

    /**
     * Show/Hide the property's value.
     *
     * @param  boolean $show Show or hide the range value.
     */
    public function setShowRangeValue($show): static
    {
        $this->showRangeValue = (bool)$show;
        $this->setRangeValueLocation($show);

        return $this;
    }

    /**
     * Determine if the property's value should be displayed.
     */
    public function showRangeValue(): bool
    {
        return $this->showRangeValue;
    }

    /**
     * Set where the property value should be displayed.
     *
     * @param  mixed $location The location to display the range value.
     *     Either "prefix", "suffix", or a custom CSS query selector.
     *     If the custom location is not a fullly-qualified ID or class name
     *     CSS selector, the query selector lookup will be done with the input's
     *     ID prefix (e.g., "my_range_output" → `#input_5db6fc900736b_my_range_output`).
     * @throws InvalidArgumentException If the show flag is invalid.
     */
    public function setRangeValueLocation($location): static
    {
        switch ($location) {
            case false:
            case null:
                $this->rangeValueLocation = null;
                return $this;

            case true:
                $this->rangeValueLocation = 'suffix';
                return $this;
        }

        // Support custom locations
        if (is_string($location)) {
            $this->rangeValueLocation = $location;
            return $this;
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid range value location: %s ',
            (get_debug_type($location))
        ));
    }

    /**
     * Retrieve where the property value should be displayed.
     *
     * @return boolean
     */
    public function rangeValueLocation()
    {
        return $this->rangeValueLocation;
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     */
    #[\Override]
    public function controlDataForJs(): array
    {
        return [
            // Base Control
            'input_name'           => $this->inputName(),

            // Range Control
            'show_range_value'     => $this->showRangeValue(),
            'range_value_location' => $this->rangeValueLocation(),
        ];
    }
}
