<?php

namespace Charcoal\Admin\Property\Input;

use App\Model\Content\AbstractContent;
use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Loader\CollectionLoaderAwareTrait;
use Charcoal\Model\Model;
use Charcoal\Translator\Translation;
use InvalidArgumentException;
use Pimple\Container;

/**
 * Class TabulatorInput
 */
class TabulatorInput extends AbstractPropertyInput
{
    use CollectionLoaderAwareTrait;

    /**
     * Settings for {@link https://github.com/olifolkerd/tabulator Bootstrap Datetabulator}.
     *
     * @var array
     */
    private $tabulatorOptions;

    /**
     * @var array
     */
    private $tabulatorColumns;

    /**
     * Set the color tabulator's options.
     *
     * This method always merges default settings.
     *
     * @param array $settings The color tabulator options.
     * @return self Chainable
     */
    public function setTabulatorOptions(array $settings)
    {
        $this->tabulatorOptions = array_merge($this->defaultTabulatorOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) color tabulator options.
     *
     * @param array $settings The color tabulator options.
     * @return self Chainable
     */
    public function mergeTabulatorOptions(array $settings)
    {
        $this->tabulatorOptions = array_merge($this->tabulatorOptions, $settings);

        return $this;
    }

    /**
     * Retrieve the color tabulator's options.
     *
     * @return array
     */
    public function tabulatorOptions(): array
    {
        if ($this->tabulatorOptions === null) {
            $this->tabulatorOptions = $this->defaultTabulatorOptions();
        }

        return $this->tabulatorOptions;
    }

    /**
     * Retrieve the default color tabulator options.
     *
     * @return array
     */
    public function defaultTabulatorOptions(): array
    {
        return [
            'allow_reorder'    => false,
            'allow_add'        => true,
            'allow_remove'     => true,
            'resizableRows'    => false,
            'resizableColumns' => false,
            'layout'           => 'fitColumns',
            'addRowPos'        => 'bottom',
            'history'          => true,
            'validationMode'   => 'highlight',
            'empty_table_message' => 'The table is empty',
        ];
    }

    /**
     * Retrieve the tabulator's properties.
     *
     * @return array
     */
    public function tabulatorColumns(): array
    {
        if ($this->tabulatorColumns === null) {
            $this->tabulatorColumns = $this->defaultTabulatorColumns();
        }

        return $this->tabulatorColumns;
    }

    /**
     * Set the color tabulator's options.
     *
     * This method always merges default settings.
     *
     * @param array $settings The color tabulator options.
     * @return self Chainable
     */
    public function setTabulatorColumns(array $settings)
    {
        $this->tabulatorColumns = array_merge($this->defaultTabulatorColumns(), $settings);

        return $this;
    }

    /**
     * @return array
     */
    public function defaultTabulatorColumns() : array
    {
        return [];
    }

    /**
     * Retrieve the data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        $selector = '#'.$this->inputId();
        $options  = $this->tabulatorOptions();

        if (isset($options['wrap']) && $options['wrap']) {
            $selector .= '_wrap';
        }

        return [
            'tabulator_selector'   => $selector,
            'tabulator_columns'    => $this->formatColumnsForJs(),
            'tabulator_options'    => $this->tabulatorOptions(),
            'multiple_options'     => $this->property()['multipleOptions'] ?? [],
        ];
    }

    /**
     * @return array
     */
    private function formatColumnsForJs() : array
    {
        $columns = $this->tabulatorColumns();
        $t = $this->translator();
        $formattedColumns = [];

        foreach ($columns as $colIdent => $colParams) {
            $formattedColumn = [
                'field'         => $colIdent,
                'title'         => $this->columnTitle($colIdent, $colParams),
                'headerTooltip' => (string)$t->translation(($colParams['description'] ?? '')) ?: false,
                'options'       => [],
            ];

            $defaultValue = $colParams['default'] ?? null;
            if (is_string($defaultValue) || is_array($defaultValue)) {
                $defaultValue = $t->translation($defaultValue);
            }

            // Auto set editor, formatter and there editorParams
            if (isset($colParams['type'])) {
                $formattedColumn = array_merge($formattedColumn, $this->defaultColumnOptionsByType($colParams['type'], $colParams));
            }

            // Auto add classes based on the params.
            $formattedColumn['cssClass'] = $this->columnCssClasses($colParams);

            // Auto add validators based on the params.
            $formattedColumn['validator'] = $this->columnValidators($colParams);

            // Merge everything together.
            $formattedColumn = array_merge($this->defaultColumnOptions(), $formattedColumn, $colParams);

            // Translatable properties.
            if (isset($colParams['l10n']) && $colParams['l10n'] === true) {
                foreach (array_keys($t->locales()) as $locale) {
                    $formattedColumn['field'] = sprintf('%s[%s]', $colIdent, $locale);
                    $formattedColumn['options']['language'] = $locale;
                    $formattedColumn['options']['default_value'] = ($defaultValue instanceof Translation) ? (string)$defaultValue[$locale] : $defaultValue;
                    $this->cleanupColumnParams($formattedColumn);
                    $formattedColumns[] = $formattedColumn;
                }
            // Untranslatable properties.
            } else {
                $formattedColumn['options']['default_value'] = (string)$defaultValue;
                $this->cleanupColumnParams($formattedColumn);
                $formattedColumns[] = $formattedColumn;
            }
        }

        return $formattedColumns;
    }

    protected function columnTitle($colIdent, $colParams) : string
    {
        $title = $colIdent;

        if (!empty($colParams['label'])) {
            $title = (string)$this->translator()->translation(($colParams['label'] ?? ''));
        } elseif (isset($colParams['label']) && $colParams['label'] === false) {
            $title = '';
        }

        // Add asterisk to required params
        if (!empty($colParams['required']) && $colParams['required'] === true) {
            $title .= '*';
        }

        return $title;
    }

    protected function defaultColumnOptionsByType($type, $colParams = []) : array
    {
        $params = [
            'editor'          => 'input',
            'editorParams'    => [],
        ];

        switch ($type) {
            case 'bool':
            case 'boolean':
                $params['editor'] = 'tickCross';
                $params['editorParams'] = [
                    'trueValue'          => 1,
                    'falseValue'         => 0,
                    'tristate'           => 0,
                    'indeterminateValue' => null,
                ];
                $params['formatter'] = 'chip';
                break;

            case 'date': {
                $params['editor'] = 'date';
                $params['editorParams'] = [
                    'format' => 'iso',
                ];
                $params['formatter'] = 'datetime';
                $params['formatterParams'] = [
                    'inputFormat' => 'iso',
                    'outputFormat' => $colParams['format'] ?? null,
                ];
            }
        }

        return $params;
    }

    protected function defaultColumnOptions() : array
    {
        return [
            'headerSort' => false,
            'resizable' => false,
        ];
    }

    protected function cleanupColumnParams(&$colParams) : void
    {
        unset($colParams['default']);
        unset($colParams['description']);
        unset($colParams['format']);
        unset($colParams['l10n']);
        unset($colParams['label']);
        unset($colParams['placeholder']);
        unset($colParams['required']);
        unset($colParams['type']);
        unset($colParams['unique']);
    }

    protected function columnValidators($colParams) : array
    {
        $validators = [];

        if (isset($colParams['validator'])) {
            $validators = $colParams['validator'];
        }

        if (!is_array($validators)) {
            $validators = [$validators];
        }

        // Automatically add the "require" validator when the column is required
        if (!empty($colParams['required']) && $colParams['required'] === true) {
            $validators[] = 'required';
        }

        // Automatically add the "unique" validator when the column is set to unique
        if (!empty($colParams['unique']) && $colParams['unique'] === true) {
            $validators[] = 'unique';
        }

        // Automatically add the "validUrl" validator when the column is set to be an URL
        if (!empty($colParams['type']) && $colParams['type'] === 'url') {
            $validators[] = 'validUrl';
        }

        return $validators;
    }

    protected function columnCssClasses($colParams) : string
    {
        $classes = [];

        if (isset($colParams['cssClass'])) {
            $classes = $colParams['cssClass'];
        }

        if (!is_array($classes)) {
            $classes = [$classes];
        }

        // Automatically add the .has-tooltip class when a tooltip is set.
        if (!empty($colParams['description'])) {
            $classes[] = 'has-tooltip';
        }

        return implode(' ', $classes);
    }

    public function addLabel()
    {
        if (!empty($this->tabulatorOptions['add_label'])) {
            return $this->translator()->translation($this->tabulatorOptions()['add_label'] ?? '');
        }

        return null;
    }

    /**
     * @param \Pimple\Container $container Services container.
     *
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        $this->setCollectionLoader($container['model/collection/loader']);
        parent::setDependencies($container);
    }
}
