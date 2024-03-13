<?php

namespace Charcoal\Admin\Property\Input;

use Charcoal\Admin\Property\AbstractPropertyInput;
use InvalidArgumentException;

/**
 * Tabulator Input Property
 *
 * {@link https://github.com/olifolkerd/tabulator Tabulator} is a JS framework
 * that allows to create interactive tables from diverse sources of data.
 */
class TabulatorInput extends AbstractPropertyInput
{
    /**
     * Settings for {@link https://github.com/olifolkerd/tabulator Tabulator}.
     *
     * @var ?array<string, mixed>
     */
    private ?array $tabulatorOptions = null;

    /**
     * Set the input's options.
     *
     * This method always merges default options.
     *
     * @param array<string, mixed> $options The input options.
     */
    public function setInputOptions(array $options): self
    {
        parent::setInputOptions($options);

        $this->finalizeInputOptions();

        return $this;
    }

    /**
     * Merge (replacing or adding) input options.
     *
     * @param array<string, mixed> $options The input options.
     */
    public function mergeInputOptions(array $options): self
    {
        $this->inputOptions = array_merge(
            (array)$this->inputOptions,
            $options
        );

        $this->finalizeInputOptions();

        return $this;
    }

    /**
     * Add (or replace) an input option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     */
    public function addInputOption(string $key, $val): self
    {
        // Make sure default options are loaded.
        if ($this->inputOptions === null) {
            $this->getInputOptions();
        }

        $this->inputOptions[$key] = $val;

        $this->finalizeInputOptions();

        return $this;
    }

    /**
     * Retrieve the default input options.
     *
     * @return array<string, mixed>
     */
    public function getDefaultInputOptions(): array
    {
        $translator = $this->translator();

        return [
            'addColumn'             => false,
            'addColumnLabel'        => $translator->trans('Add Column'),
            'addRow'                => false,
            'addRowLabel'           => $translator->trans('Add Row'),
            'autoColumnStartIndex'  => 0,
            'autoColumnTemplates'   => [],
            'columnsManipulateData' => false,
            'newColumnData'         => null,
            'newRowData'            => null,
            'storableRowRange'      => null,
            'validateOn'            => [],
        ];
    }

    /**
     * Set the Tabulator's options.
     *
     * This method always merges default options.
     *
     * @param array<string, mixed> $options The Tabulator options.
     */
    public function setTabulatorOptions(array $options): self
    {
        $this->mergeTabulatorOptions($options);

        return $this;
    }

    /**
     * Merge (replacing or adding) Tabulator options.
     *
     * @param array<string, mixed> $options The Tabulator options.
     */
    public function mergeTabulatorOptions(array $options): self
    {
        $this->tabulatorOptions = array_merge(
            (array)$this->tabulatorOptions,
            $options
        );

        $this->finalizeTabulatorOptions();

        return $this;
    }

    /**
     * Add (or replace) a Tabulator option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     */
    public function addTabulatorOption(string $key, $val): self
    {
        $this->tabulatorOptions[$key] = $val;

        $this->finalizeTabulatorOptions();

        return $this;
    }

    /**
     * Retrieve the Tabulator's options.
     *
     * @return array<string, mixed>
     */
    public function getTabulatorOptions(): array
    {
        if ($this->tabulatorOptions === null) {
            $this->tabulatorOptions = $this->getDefaultTabulatorOptions();
        }

        return $this->tabulatorOptions;
    }

    /**
     * Retrieve the default Tabulator options.
     *
     * @return array<string, mixed>
     */
    public function getDefaultTabulatorOptions(): array
    {
        return [
            'layout'    => 'fitColumns',
            'addRowPos' => 'bottom',
            'history'   => true,
        ];
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs(): array
    {
        $inputOptions = $this->getInputOptions();
        $tabulatorOptions = $this->getTabulatorOptions();
        $tabulatorSelector = '#' . $this->inputId();

        if (isset($tabulatorOptions['history']) && !$tabulatorOptions['history']) {
            $inputOptions['undo'] = false;
            $inputOptions['redo'] = false;
        }

        if (isset($tabulatorOptions['wrap']) && $tabulatorOptions['wrap']) {
            $tabulatorSelector .= '_wrap';
        }

        return [
            'input_options'      => $inputOptions,
            'tabulator_selector' => $tabulatorSelector,
            'tabulator_options'  => $tabulatorOptions,
        ];
    }

    protected function finalizeInputOptions(): void
    {
        if (
            (isset($this->inputOptions['addRow']) && $this->inputOptions['addRow']) ||
            (isset($this->inputOptions['addColumn']) && $this->inputOptions['addColumn'])
        ) {
            $this->inputOptions['addColumnOrRow'] = true;
        }

        if (isset($this->inputOptions['addColumnLabel'])) {
            $this->inputOptions['addColumnLabel'] = $this->translator()->translate(
                $this->inputOptions['addColumnLabel']
            );
        }

        if (isset($this->inputOptions['addRowLabel'])) {
            $this->inputOptions['addRowLabel'] = $this->translator()->translate(
                $this->inputOptions['addRowLabel']
            );
        }
    }

    protected function finalizeTabulatorOptions(): void
    {
        if (isset($this->tabulatorOptions['placeholder'])) {
            $this->tabulatorOptions['placeholder'] = $this->translator()->translate(
                $this->tabulatorOptions['placeholder']
            );
        }

        if (isset($this->tabulatorOptions['autoColumnTemplates'])) {
            foreach ($this->tabulatorOptions['autoColumnTemplates'] as &$column) {
                $column = $this->resolveTabulatorColumnDefinition($column);
            }
        }

        if (isset($this->tabulatorOptions['columns'])) {
            foreach ($this->tabulatorOptions['columns'] as &$column) {
                $column = $this->resolveTabulatorColumnDefinition($column);
            }
        }
    }

    /**
     * @param  array<string, mixed> $column The column to resolve.
     * @return array<string, mixed> The resolved column.
     */
    protected function resolveTabulatorColumnDefinition(array $column): array
    {
        if (isset($column['title'])) {
            $column['title'] = $this->translator()->translate($column['title']);
        }

        if (isset($column['tooltip'])) {
            $column['tooltip'] = $this->translator()->translate($column['tooltip']);
        }

        return $column;
    }
}
