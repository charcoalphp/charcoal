<?php

namespace Charcoal\Admin\Property\Input;

use App\Model\Content\AbstractContent;
use Charcoal\Admin\Property\AbstractPropertyInput;
use Charcoal\Loader\CollectionLoaderAwareTrait;
use Charcoal\Model\Model;
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
            $title = $colIdent;

            if (!empty($colParams['label'])) {
                $title = (string)$t->translation(($colParams['label'] ?? ''));
            }

            // Add asterisk to required params
            if (!empty($colParams['required']) && $colParams['required'] === true) {
                $title .= '*';
            }

            $formattedColumn = [
                'field'         => $colIdent,
                'title'         => $title,
                'editor'        => ($colParams['editor'] ?? $this->cellEditorByType($colParams['type'])),
                'editorParams'  => ($colParams['editorParams'] ?? []),
                'headerTooltip' => (string)$t->translation(($colParams['description'] ?? '')) ?: false,
                'validator'     => $this->columnValidators($colParams),
                'options'       => [],
            ];


            // Remove options that are not supported by Tabulator
            unset($colParams['label']);
            unset($colParams['type']);
            unset($colParams['required']);
            unset($colParams['l10n']);
            unset($colParams['unique']);
            unset($colParams['placeholder']);
            unset($colParams['validator']);

            $formattedColumn = array_merge($this->defaultColumnOptions(), $formattedColumn, $colParams);

            // Translatable properties.
            if (isset($colParams['l10n']) && $colParams['l10n'] === true) {
                foreach (array_keys($t->locales()) as $locale) {
                    $formattedColumn['field'] = sprintf('%s[%s]', $colIdent, $locale);
                    $formattedColumn['options']['language'] = $locale;
                    $formattedColumns[] = $formattedColumn;
                }
            // Untranslatable properties.
            } else {
                $formattedColumns[] = $formattedColumn;
            }
        }

        return $formattedColumns;
    }

    protected function defaultColumnOptions() : array
    {
        return [
            'headerSort' => false,
        ];
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

        if (!empty($colParams['required']) && $colParams['required'] === true) {
            $validators[] = 'required';
        }

        if (!empty($colParams['unique']) && $colParams['unique'] === true) {
            $validators[] = 'unique';
        }

        return $validators;
    }

    protected function cellEditorByType() : string
    {
        return 'input';
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
