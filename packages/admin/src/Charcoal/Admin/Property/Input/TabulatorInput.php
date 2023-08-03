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
    private $tabulatorProperties;

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
     * Add (or replace) an color tabulator option.
     *
     * @param string $key The setting to add/replace.
     * @param mixed  $val The setting's value to apply.
     * @return self Chainable
     * @throws InvalidArgumentException If the identifier is not a string.
     */
    public function addTabulatorOption($key, $val)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->tabulatorOptions === null) {
            $this->tabulatorOptions();
        }

        $this->tabulatorOptions[$key] = $val;

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
            'layout'       => 'fitColumns',
            'addRowPos'    => 'bottom',
            'history'      => true,
        ];
    }

    /**
     * Retrieve the tabulator's properties.
     *
     * @return array
     */
    public function tabulatorProperties(): array
    {
        if ($this->tabulatorProperties === null) {
            $this->tabulatorProperties = $this->defaultTabulatorProperties();
        }

        return $this->tabulatorProperties;
    }

    /**
     * Set the color tabulator's options.
     *
     * This method always merges default settings.
     *
     * @param array $settings The color tabulator options.
     * @return self Chainable
     */
    public function setTabulatorProperties(array $settings)
    {
        $this->tabulatorProperties = array_merge($this->defaultTabulatorProperties(), $settings);

        return $this;
    }

    /**
     * @return array
     */
    public function defaultTabulatorProperties() : array
    {
        return [];
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     *
     * @return array
     */
    public function controlDataForJs()
    {
        $options  = $this->tabulatorOptions();
        $properties  = $this->parsedTabulatorProperties();
        $selector = '#'.$this->inputId();

        if (isset($options['wrap']) && $options['wrap']) {
            $selector .= '_wrap';
        }

        return [
            'tabulator_selector'   => $selector,
            'tabulator_properties' => $properties,
            'tabulator_options'    => $options,
        ];
    }

    /**
     * @return array
     */
    private function parsedTabulatorProperties() : array
    {
        $properties = $this->tabulatorProperties();
        $parsed = [];

        foreach ($properties as $propIdent => $propOptions) {
            $fieldName = $propIdent;
            $fieldTitle = (string)$this->translator()->translation(($propOptions['label'] ?? ''));
            $fieldDescription = (string)$this->translator()->translation(($propOptions['description'] ?? ''));
            $fieldEditor = ($propOptions['editor'] ?? 'input');
            $editorParams = ($propOptions['editorParams'] ?? []);
            $propSettings = [];

            if (isset($propOptions['autocompleteSource'])) {
                $values = $this->fetchAutocompleteValues($propOptions['autocompleteSource']);
                if (!empty($values)) {
                    $editorParams['values'] = $values;
                }
            }


            // Translatable properties.
            if (isset($propOptions['l10n']) && $propOptions['l10n'] === true) {
                foreach (array_keys($this->translator()->locales()) as $locale) {
                    $propSettings['language'] = $locale;
                    $fieldName = sprintf('%s[%s]', $propIdent, $locale);
                    $parsed[] = [
                        'field'         => $fieldName,
                        'title'         => $fieldTitle,
                        'editor'        => $fieldEditor,
                        'editorParams'  => $editorParams,
                        'headerTooltip' => $fieldDescription ?: false,
                        'resizable'     => false,
                        'headerSort'    => false,
                        'settings'      => $propSettings,
                    ];
                }
                // Untranslatable properties.
            } else {
                $parsed[] = [
                    'field'        => $fieldName,
                    'title'        => $fieldTitle,
                    'editor'       => $fieldEditor,
                    'editorParams' => $editorParams,
                    'headerTooltip' => $fieldDescription ?: false,
                    'resizable'    => false,
                    'headerSort'    => false,
                    'settings'     => $propSettings,
                ];
            }
        }

        return $parsed;
    }

    /**
     * @param string $autocompleteSource Model class.
     *
     * @return array
     */
    private function fetchAutocompleteValues($autocompleteSource) : array
    {
        $values = [];
        try {
            $loader = $this->collectionLoader()->setModel($autocompleteSource);
            $results = $loader->reset()->addFilter('active', 1)->load();

            $models = $results->values();

            if ($models) {
                $values = array_map(fn(AbstractContent $model) => $model->name(), $models);
            }
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('[Tabulator] %s', $exception->getMessage()));
        }

        return $values;
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
