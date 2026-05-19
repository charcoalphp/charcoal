<?php

namespace Charcoal\Admin\Docs\Widget;

use Charcoal\Admin\Widget\FormPropertyWidget;

/**
 * DocFormPropertyWidget
 */
class DocFormPropertyWidget extends FormPropertyWidget
{
    /**
     * @var array
     */
    protected $extraData;

    /**
     * @var array
     */
    protected $displayOptions;

    public function hasExtraData(): bool
    {
        return (bool) count($this->extraData());
    }

    /**
     * @return boolean
     */
    public function collapsible()
    {
        $displayOps = $this->displayOptions();

        return $displayOps['collapsible'] ?? false;
    }

    /**
     * @return boolean
     */
    public function collapsed()
    {
        $displayOps = $this->displayOptions();

        return $displayOps['collapsed'] ?? false;
    }

    /**
     * @return boolean
     */
    public function parented()
    {
        $displayOps = $this->displayOptions();

        return $displayOps['parented'] ?? false;
    }

    /**
     * @return \Charcoal\Translator\Translation|null|string
     */
    public function typeDescription()
    {
        $type = $this->prop()->type();

        return match ($type) {
            'boolean' => $this->translator()->translation('
                    The field is a TRUE | FALSE statement
                '),
            'image', 'audio', 'file' => $this->translator()->translation('
                    The field will ask to upload a file using the file manager
                '),
            'string', 'text' => $this->translator()->translation('
                    The field is a simple text input
                '),
            'object' => $this->translator()->translation('
                    The field is a relation to another object in the back-end (ex: a category object)
                '),
            'date-time' => $this->translator()->translation('
                    The field requires a date and will prompt a date picker<br>
                    as an easy way to provide it in a supported format
                '),
            default => '',
        };
    }

    /**
     * @return array
     */
    public function extraData()
    {
        if ($this->extraData !== null) {
            return $this->extraData;
        }

        $prop = $this->prop();
        $out = [];

        if ($prop['l10n']) {
            $out[] = [
                'feature'     => $this->translator()->translation('multilingual'),
                'description' => $this->translator()->translation('
                    The field needs to be filled in more than one language
                ')
            ];
        }

        if ($prop['multiple']) {
            $out[] = [
                'feature'     => $this->translator()->translation('multiple'),
                'description' => $this->translator()->translation('
                    The field accepts more than one input
                ')
            ];
        }

        if ($prop['required']) {
            $out[] = [
                'feature'     => $this->translator()->translation('required'),
                'description' => $this->translator()->translation('
                    The field is required and will prevent saving or updating if empty
                ')
            ];
        }

        return $out;
    }

    /**
     * @return array|mixed
     */
    public function displayOptions()
    {
        return $this->displayOptions;
    }

    /**
     * @param array|mixed $displayOptions The display options array.
     * @throws \InvalidArgumentException If argument is not of type "array".
     */
    public function setDisplayOptions($displayOptions): static
    {
        if (!is_array($displayOptions)) {
            throw new \InvalidArgumentException('display_options should be of type array');
        }

        $this->displayOptions = $displayOptions;

        return $this;
    }
}
