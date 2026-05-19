<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
use Charcoal\Admin\Property\AbstractPropertyInput;

/**
 * Date/Time Picker Input Property
 */
class DateTimePickerInput extends AbstractPropertyInput
{
    public const DEFAULT_JS_FORMAT = 'YYYY-MM-DD HH:mm:ss';

    /**
     * @var string $inputGroupClass
     */
    protected $inputGroupClass = '';

    /**
     * Settings for {@link https://eonasdan.github.io/bootstrap-datetimepicker/ Bootstrap Datepicker}.
     */
    private ?array $pickerOptions = null;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    public function type(): string
    {
        return 'datetime-local';
    }

    /**
     * @param string $class The input group class attribute.
     * @throws InvalidArgumentException If the class is not a string.
     */
    public function setInputGroupClass($class): static
    {
        if (!is_string($class)) {
            throw new InvalidArgumentException('CSS Class(es) must be a string');
        }
        $this->inputGroupClass = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function inputGroupClass()
    {
        return $this->inputGroupClass;
    }

    /**
     * Set the color picker's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The color picker options.
     * @return ColorpickerInput Chainable
     */
    public function setPickerOptions(array $settings): static
    {
        $this->pickerOptions = array_merge($this->defaultPickerOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) color picker options.
     *
     * @param  array $settings The color picker options.
     * @return ColorpickerInput Chainable
     */
    public function mergePickerOptions(array $settings): static
    {
        $this->pickerOptions = array_merge($this->pickerOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an color picker option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return ColorpickerInput Chainable
     */
    public function addPickerOption($key, $val): static
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->pickerOptions === null) {
            $this->pickerOptions();
        }

        $this->pickerOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the color picker's options.
     */
    public function pickerOptions(): array
    {
        if ($this->pickerOptions === null) {
            $this->pickerOptions = $this->defaultPickerOptions();
        }

        return $this->pickerOptions;
    }

    /**
     * Retrieve the default color picker options.
     */
    public function defaultPickerOptions(): array
    {
        $date = null;

        if ($this->inputVal() !== '') {
            $date = new \DateTime($this->inputVal());
        }

        return [
            'format'      => self::DEFAULT_JS_FORMAT,
            'defaultDate' => $date instanceof \DateTime ? $date->format(\DateTime::ISO8601) : null
        ];
    }

    /**
     * Retrieve the color picker's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function pickerOptionsAsJson()
    {
        return json_encode($this->pickerOptions());
    }
}
