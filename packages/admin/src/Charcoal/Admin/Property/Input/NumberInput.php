<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Number Property Input Type
 */
class NumberInput extends AbstractPropertyInput
{
    /**
     * The minimum numeric value allowed.
     */
    private null|int|float $min = null;

    /**
     * The maximum numeric value allowed.
     */
    private null|int|float $max = null;

    /**
     * Limit the increments at which a numeric value can be set.
     *
     * Note: It can be the string "any" or a positive floating point number.
     */
    private null|string|int|float $step = null;

    /**
     * @param  mixed $min The minimum.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMin($min): static
    {
        if ($min === null || $min === '') {
            $this->min = null;
            return $this;
        }

        if (!is_numeric($min)) {
            throw new InvalidArgumentException(
                'Minimum value needs to be a number'
            );
        }

        $this->min = ($min + 0);
        return $this;
    }

    public function hasMin(): bool
    {
        return !(empty($this->min) && !is_numeric($this->min));
    }

    public function min(): int|float|null
    {
        return $this->min;
    }

    /**
     * @param  mixed $max The maximum.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMax($max): static
    {
        if ($max === null || $max === '') {
            $this->max = null;
            return $this;
        }

        if (!is_numeric($max)) {
            throw new InvalidArgumentException(
                'Maximum value needs to be a number'
            );
        }

        $this->max = ($max + 0);
        return $this;
    }

    public function hasMax(): bool
    {
        return !(empty($this->max) && !is_numeric($this->max));
    }

    public function max(): int|float|null
    {
        return $this->max;
    }

    /**
     * @param  mixed $step The step attribute.
     * @throws InvalidArgumentException If the value is not a number.
     * @return Text Chainable
     */
    public function setStep($step): static
    {
        if ($step === null || $step === '') {
            $this->step = null;
            return $this;
        }

        if ($step === 'any') {
            $this->step = $step;
            return $this;
        }

        if (!is_numeric($step)) {
            throw new InvalidArgumentException(
                'Step size needs to be a number or the string "any"'
            );
        }

        $this->step = ($step + 0);
        return $this;
    }

    public function hasStep(): bool
    {
        return !(empty($this->step) && !is_numeric($this->step));
    }

    public function step(): string|int|float|null
    {
        return $this->step;
    }
}
