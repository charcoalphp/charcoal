<?php

declare(strict_types=1);

namespace Charcoal\Admin\Widget;

use Charcoal\Admin\Widget\ObjectFormWidget;
use Charcoal\Admin\Ui\HasLanguageSwitcherInterface;
use Charcoal\Admin\Ui\HasLanguageSwitcherTrait;

/**
 * The quick form widget for editing objects on the go.
 */
class QuickFormWidget extends ObjectFormWidget implements
    HasLanguageSwitcherInterface
{
    use HasLanguageSwitcherTrait;

    /**
     * Ident for tab display.
     *
     * @const string
     */
    public const DISPLAY_MODE_TAB = 'tab';

    /**
     * Ident for lang tab display.
     *
     * @const string
     */
    public const DISPLAY_MODE_LANG = 'lang';

    /**
     * @param  array $data The widget data.
     */
    #[\Override]
    public function setData(array $data): static
    {
        parent::setData($data);

        if ($this->groupDisplayMode() === self::DISPLAY_MODE_LANG) {
            $this->setTabsTemplate('charcoal/admin/template/form/nav-tabs-languages');
        }

        return $this;
    }

    /**
     * Retrieve the identifier of the form to use, or its fallback.
     *
     * @see    ObjectFormWidget::formIdentFallback()
     * @return string
     */
    #[\Override]
    public function formIdentFallback()
    {
        $metadata = $this->obj()->metadata();

        if (isset($metadata['admin']['default_quick_form'])) {
            return $metadata['admin']['default_quick_form'];
        }

        if (isset($this->formData()['form_ident'])) {
            $ident = $this->formData()['form_ident'];

            if (is_string($ident) && ($ident !== '' && $ident !== '0')) {
                return $ident;
            }
        }

        return 'quick';
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     */
    #[\Override]
    public function widgetDataForJs(): array
    {
        return array_merge_recursive(
            parent::widgetDataForJs(),
            [
                'is_display_mode_lang' => $this->isDisplayModeLang(),
                'show_language_switch' => $this->showLanguageSwitch(),
            ]
        );
    }

    /**
     * Retrieve the label for the form submission button.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    #[\Override]
    public function submitLabel()
    {
        if (isset($this->formData()['submit_label'])) {
            $label = $this->formData()['submit_label'];
            $this->submitLabel = $this->translator()->translation($label);
        }

        return parent::submitLabel();
    }

    /**
     * @see    HasLanguageSwitcherTrait::showLanguageSwitch()
     */
    protected function resolveShowLanguageSwitch(): bool
    {
        return $this->supportsLanguageSwitch();
    }

    /**
     * Determine if content groups are to be displayed as languages tabbable panes.
     */
    public function isDisplayModeLang(): bool
    {
        return ($this->groupDisplayMode() === self::DISPLAY_MODE_LANG);
    }

    /**
     * Determine if content groups are to be displayed as tabbable panes.
     */
    #[\Override]
    public function isTabbable(): bool
    {
        return in_array($this->groupDisplayMode(), $this->getTabbableDisplayModes());
    }

    public function getTabbableDisplayModes(): array
    {
        return [
            self::DISPLAY_MODE_TAB,
            self::DISPLAY_MODE_LANG
        ];
    }

    /**
     * @return string
     */
    public function availableLanguagesAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->languages(), $options);
    }

    #[\Override]
    public function defaultFormTabsTemplate(): string
    {
        return 'charcoal/admin/template/form/nav-tabs';
    }
}
