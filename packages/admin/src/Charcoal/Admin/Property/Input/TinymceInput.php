<?php

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
// From Mustache
use Mustache\LambdaHelper as LambdaHelper;
// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextareaInput;

/**
 * TinyMCE Rich-Text Input Property
 */
class TinymceInput extends TextareaInput
{
    /**
     * The TinyMCE editor settigns.
     *
     * @var array
     */
    private $editorOptions;

    /**
     * Label for the file picker dialog.
     *
     * @var \Charcoal\Translator\Translation|string|null
     */
    private $dialogTitle;

    /**
     * Flag wether the "file picker" popup button should be displaed.
     */
    private ?bool $showFilePicker = null;

    /**
     * URL for the "file picker" popup.
     *
     * @var string
     */
    private $filePickerUrl;

    /**
     * Set the editor's options.
     *
     * This method always merges default settings.
     *
     * @param  array $settings The editor options.
     * @return Tinymce Chainable
     */
    public function setEditorOptions(array $settings): static
    {
        $this->editorOptions = array_merge($this->defaultEditorOptions(), $settings);

        return $this;
    }

    /**
     * Merge (replacing or adding) editor options.
     *
     * @param  array $settings The editor options.
     * @return Tinymce Chainable
     */
    public function mergeEditorOptions(array $settings): static
    {
        $this->editorOptions = array_merge($this->editorOptions, $settings);

        return $this;
    }

    /**
     * Add (or replace) an editor option.
     *
     * @param  string $key The setting to add/replace.
     * @param  mixed  $val The setting's value to apply.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return Tinymce Chainable
     */
    public function addEditorOption($key, $val): static
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Setting key must be a string.'
            );
        }

        // Make sure default options are loaded.
        if ($this->editorOptions === null) {
            $this->editorOptions();
        }

        $this->editorOptions[$key] = $val;

        return $this;
    }

    /**
     * Retrieve the editor's options.
     *
     * @return array
     */
    public function editorOptions()
    {
        if ($this->editorOptions === null) {
            $this->editorOptions = $this->defaultEditorOptions();
        }

        return $this->editorOptions;
    }

    /**
     * Retrieve the default editor options.
     *
     * @return array
     */
    public function defaultEditorOptions()
    {
        $defaultData = $this->metadata()->defaultData();

        return ($defaultData['editor_options'] ?? []);
    }

    /**
     * Retrieve the editor's options as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function editorOptionsAsJson()
    {
        return json_encode($this->editorOptions());
    }

    /**
     * Set the title for the file picker dialog.
     *
     * @param  mixed $title The dialog title.
     */
    public function setDialogTitle($title): static
    {
        $this->dialogTitle = $this->translator()->translation($title);

        return $this;
    }

    /**
     * Retrieve the default title for the file picker dialog.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    protected function defaultDialogTitle(): ?\Charcoal\Translator\Translation
    {
        return $this->translator()->translation('filesystem.library.media');
    }

    /**
     * Retrieve the title for the file picker dialog.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function dialogTitle()
    {
        if ($this->dialogTitle === null) {
            $this->setDialogTitle($this->defaultDialogTitle());
        }

        return $this->dialogTitle;
    }

    /**
     * @param boolean $show The show file picker flag.
     * @return FileInput Chainable
     */
    public function setShowFilePicker($show): static
    {
        $this->showFilePicker = (bool)$show;

        return $this;
    }

    /**
     * @return boolean
     */
    public function showFilePicker()
    {
        if ($this->showFilePicker === null) {
            return $this->hasFilePicker();
        }

        return $this->showFilePicker;
    }

    public function hasFilePicker(): bool
    {
        return class_exists('\\elFinder');
    }

    /**
     * @param  string $url The file picker AJAX URL.
     * @return FileInput Chainable
     */
    public function setFilePickerUrl($url): static
    {
        $this->filePickerUrl = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function filePickerUrl()
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        return $this->filePickerUrl;
    }

    /**
     * Render the file picker URL with the correct object model context.
     *
     * This method (a necessary evil) allows one to customize the URL
     * without duplicating the template view.
     *
     * @see \Charcoal\Admin\Property\Input\FileInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareFilePickerUrl(): ?\Closure
    {
        if (!$this->showFilePicker()) {
            return null;
        }

        $uri = $this->getFilePickerUrlTemplate();

        return function ($noop, LambdaHelper $helper) use ($uri): null {
            $uri = $helper->render($uri);
            $this->setFilePickerUrl($uri);

            return null;
        };
    }

    /**
     * Retrieve the elFinder connector URL template for rendering.
     */
    protected function getFilePickerUrlTemplate(): string
    {
        $uri = 'obj_type={{ objType }}&obj_id={{ objId }}&property={{ p.ident }}&callback={{ inputId }}';

        return '{{# withAdminUrl }}elfinder?' . $uri . '{{/ withAdminUrl }}';
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     */
    #[\Override]
    public function controlDataForJs(): array
    {
        return [
            'editor_options' => $this->editorOptions(),
            'dialog_title'   => (string)$this->dialogTitle(),
            'elfinder_url'   => $this->filePickerUrl(),
        ];
    }
}
