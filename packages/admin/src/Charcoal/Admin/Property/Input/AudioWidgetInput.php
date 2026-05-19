<?php

namespace Charcoal\Admin\Property\Input;

use Aws\signer\signerClient;
use InvalidArgumentException;
use UnexpectedValueException;
// From Mustache
use Mustache_LambdaHelper as LambdaHelper;
// From 'charcoal-view'
use Charcoal\View\ViewableInterface;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\AudioInput;

/**
 * Audio Widget Property Input
 */
class AudioWidgetInput extends AudioInput
{
    public const INPUT_TEXT    = 'text';
    public const INPUT_CAPTURE = 'capture';
    public const INPUT_UPLOAD  = 'upload';

    /**
     * Whether text-to-speech is enabled.
     */
    private bool $textEnabled = true;

    /**
     * Whether audio recording is enabled.
     */
    private bool $captureEnabled = true;

    /**
     * Whether file upload is enabled.
     */
    private bool $uploadEnabled = true;

    /**
     * URL for the "audio recorder" plugin.
     *
     * @var string
     */
    private $recorderPluginUrl;

    /**
     * The text property value for TTS.
     *
     * @var mixed
     */
    private $textPropertyVal;

    /**
     * The text property for TTS.
     */
    private ?\Charcoal\Property\PropertyInterface $textProperty = null;

    /**
     * The HTML input name attribute for TTS.
     *
     * @var string
     */
    protected $textInputName;

    /**
     * The active widget pane.
     *
     * @var string
     */
    protected $activePane;

    /**
     * The current rendering context for the audio widget.
     *
     * @var string
     */
    private $currentContext;

    /**
     * Retrieve the control type for the HTML element `<input>`.
     */
    #[\Override]
    public function type(): string
    {
        return 'hidden';
    }

    public function displayAudioWidget(): bool
    {
        return $this->textEnabled() || $this->captureEnabled() || $this->uploadEnabled();
    }

    /**
     * @param  boolean $textEnabled If TTS is enabled or not for this widget.
     */
    public function setTextEnabled($textEnabled): static
    {
        $this->textEnabled = (bool) $textEnabled;
        return $this;
    }

    public function textEnabled(): bool
    {
        return $this->textEnabled;
    }

    /**
     * @param  boolean $captureEnabled If recording is enabled or not for this widget.
     */
    public function setCaptureEnabled($captureEnabled): static
    {
        $this->captureEnabled = (bool) $captureEnabled;
        return $this;
    }

    public function captureEnabled(): bool
    {
        return $this->captureEnabled;
    }

    /**
     *
     * @param  boolean $recordingEnabled If recording is enabled or not for this widget.
     */
    #[\Deprecated(message: 'In favour of {@see self::setCaptureEnabled()}')]
    public function setRecordingEnabled($recordingEnabled): static
    {
        $this->captureEnabled = (bool) $recordingEnabled;
        return $this;
    }

    #[\Deprecated(message: 'In favour of {@see self::captureEnabled()}')]
    public function recordingEnabled(): bool
    {
        return $this->captureEnabled;
    }

    /**
     * @param  boolean $uploadEnabled If file upload is enabled or not for this widget.
     */
    public function setUploadEnabled($uploadEnabled): static
    {
        $this->uploadEnabled = (bool) $uploadEnabled;
        return $this;
    }

    public function uploadEnabled(): bool
    {
        return $this->uploadEnabled;
    }

    /**
     *
     * @param  boolean $fileEnabled If file upload is enabled or not for this widget.
     */
    #[\Deprecated(message: 'In favour of {@see self::setUploadEnabled()}')]
    public function setFileEnabled($fileEnabled): static
    {
        $this->uploadEnabled = (bool) $fileEnabled;
        return $this;
    }

    #[\Deprecated(message: 'In favour of {@see self::uploadEnabled()}')]
    public function fileEnabled(): bool
    {
        return $this->uploadEnabled;
    }

    /**
     * @param  string $url The recording/exporting plugin URL.
     */
    public function setRecorderPluginUrl($url): static
    {
        $this->recorderPluginUrl = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function recorderPluginUrl()
    {
        if (!$this->captureEnabled()) {
            return null;
        }

        return $this->recorderPluginUrl;
    }

    /**
     * Render the recording plugin URL with the correct object model context.
     *
     * This method (a necessary evil) allows one to customize the URL
     * without duplicating the template view.
     *
     * @see \Charcoal\Admin\Property\Input\FileInput::prepareFilePickerUrl()
     * @see \Charcoal\Admin\Property\Input\TinymceInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareRecorderPluginUrl()
    {
        $uri = $this->getRecorderPluginUrlTemplate();

        return function ($noop, LambdaHelper $helper) use ($uri): null {
            $uri = $helper->render($uri);
            $this->setRecorderPluginUrl($uri);

            return null;
        };
    }

    /**
     * Retrieve the elFinder connector URL template for rendering.
     *
     * This method is overriden to change the `callback` value to reflect
     * the correct input control ID.
     */
    protected function getRecorderPluginUrlTemplate(): string
    {
        $uri = 'assets/admin/scripts/vendors/recorderjs/recorder.js';

        return '{{# withBaseUrl }}' . $uri . '{{/ withBaseUrl }}';
    }

    /**
     * Set the active widget pane.
     *
     * @param  string $activePane The active widget pane.
     * @throws InvalidArgumentException If the provided argument is not a string.
     */
    public function setActivePane($activePane): static
    {
        if ($activePane === null || $activePane === '') {
            $this->activePane = null;
            return $this;
        }

        $validPanes = [
            static::INPUT_TEXT,
            static::INPUT_CAPTURE,
            static::INPUT_UPLOAD,
        ];

        if (!in_array($activePane, $validPanes)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid input "%s" for Audio Property Input',
                $activePane
            ));
        }

        $this->activePane = $activePane;
        return $this;
    }

    /**
     * Retrieve the active widget pane based on the property's values.
     *
     * @return string
     */
    public function resolveActivePane()
    {
        if ($this->hasAudioPropertyVal()) {
            return static::INPUT_UPLOAD;
        }

        return static::INPUT_TEXT;
    }

    /**
     * Retrieve the active widget pane.
     *
     * @return string
     */
    public function activePane()
    {
        if ($this->activePane === null) {
            return $this-> resolveActivePane();
        }

        return $this->activePane;
    }

    /**
     * Alias of {@see AbstractPropertyInput::setPropertyVal()}.
     *
     * @param  mixed $val The audio property value.
     */
    public function setAudioPropertyVal($val): static
    {
        $this->setPropertyVal($val);
        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::propertyVal()}.
     *
     * @return mixed
     */
    public function audioPropertyVal()
    {
        return $this->propertyVal();
    }

    public function hasAudioPropertyVal(): bool
    {
        $prop = $this->audioProperty();
        $val  = $prop->inputVal($this->audioPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        return !empty($val);
    }

    /**
     * Alias of {@see AbstractPropertyInput::setProperty()}.
     *
     * @param  PropertyInterface $p The property for TTS.
     */
    public function setAudioProperty(PropertyInterface $p): static
    {
        $this->setProperty($p);
        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::property()}.
     *
     * @return PropertyInterface
     */
    public function audioProperty()
    {
        return $this->property();
    }

    /**
     * Alias of {@see AbstractPropertyInput::propertyIdent()}.
     *
     * @return string
     */
    public function audioPropertyIdent()
    {
        return $this->propertyIdent();
    }

    /**
     * Alias of {@see AbstractPropertyInput::setInputName()}.
     *
     * @param  string $inputName HTML input id attribute.
     */
    public function setAudioInputName($inputName): static
    {
        $this->setInputName($inputName);

        return $this;
    }

    /**
     * Alias of {@see AbstractPropertyInput::inputName()}.
     *
     * @return string
     */
    public function audioInputName()
    {
        return $this->inputName();
    }

    /**
     * Alias of {@see AbstractPropertyInput::inputVal()}.
     *
     * @return string
     */
    public function audioInputVal()
    {
        return $this->inputVal();
    }

    /**
     * Set the property value for TTS.
     *
     * @param  mixed $val The property value.
     */
    public function setTextPropertyVal($val): static
    {
        $this->textPropertyVal = $val;
        return $this;
    }

    /**
     * Retrieve the property value for TTS.
     *
     * @return mixed
     */
    public function textPropertyVal()
    {
        return $this->textPropertyVal;
    }

    public function hasTextPropertyVal(): bool
    {
        $prop = $this->textProperty();
        $val  = $prop->inputVal($this->textPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        return !empty($val);
    }

    /**
     * Set the property instance for TTS.
     *
     * @param  PropertyInterface $p The property for TTS.
     */
    public function setTextProperty(PropertyInterface $p): static
    {
        $this->textProperty = $p;
        return $this;
    }

    /**
     * Retrieve the property instance for TTS.
     *
     * @return PropertyInterface
     */
    public function textProperty(): ?\Charcoal\Property\PropertyInterface
    {
        return $this->textProperty;
    }

    /**
     * Retrieve the property identifier for TTS.
     *
     * @return string
     */
    public function textPropertyIdent()
    {
        return $this->textProperty()['ident'];
    }

    /**
     * Set the input name for TTS.
     *
     * @see    AbstractPropertyInput::setInputName()
     * @param  string $inputName HTML input name attribute.
     */
    public function setTextInputName($inputName): static
    {
        $this->textInputName = $inputName;
        return $this;
    }

    /**
     * Retrieve the input name for TTS.
     *
     * @see    AbstractPropertyInput::inputName()
     * @return string
     */
    public function textInputName()
    {
        $name = $this->textInputName ?: $this->textPropertyIdent();

        if ($this->textProperty()['l10n']) {
            $name .= '[' . $this->lang() . ']';
        }

        return $name;
    }

    /**
     * Retrieve the input value for TTS.
     *
     * @see    AbstractPropertyInput::inputVal()
     * @throws UnexpectedValueException If the value is invalid.
     * @return string
     */
    public function textInputVal(): int|float|string|bool
    {
        $prop = $this->textProperty();
        $val  = $prop->inputVal($this->textPropertyVal(), [
            'lang' => $this->lang(),
        ]);

        if ($val === null) {
            return '';
        }

        if (!is_scalar($val)) {
            throw new UnexpectedValueException(sprintf(
                'Property Input Value must be a string, received %s',
                (get_debug_type($val))
            ));
        }

        return $val;
    }

    /**
     * Retrieve the input ID for the TTS property.
     */
    public function textInputId(): string
    {
        return 'audio_text_' . $this->inputId();
    }

    /**
     * Retrieve the input ID for the audio recorder property.
     */
    public function captureInputId(): string
    {
        return 'audio_capture_' . $this->inputId();
    }

    /**
     * Retrieve the input ID for the audio file property.
     */
    public function uploadInputId(): string
    {
        return 'audio_upload_' . $this->inputId();
    }

    /**
     * Retrieve the input ID for the widget's hidden property.
     */
    public function hiddenInputId(): string
    {
        return 'audio_hidden_' . $this->inputId();
    }

    /**
     * Change the property input context to that of the text-to-speech property.
     *
     * @return callable|null
     */
    public function textPropertyContext(): ?\Closure
    {
        if (!$this->textEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_TEXT;

        $baseInputId = $this->inputId();
        $textInputId = $this->textInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $textInputId) {
            $this->setInputId($textInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Change the property input context to that of the audio recorder property.
     *
     * @return callable|null
     */
    public function capturePropertyContext(): ?\Closure
    {
        if (!$this->captureEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_CAPTURE;

        $baseInputId    = $this->inputId();
        $captureInputId = $this->captureInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $captureInputId) {
            $this->setInputId($captureInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Change the property input context to that of the audio file property.
     *
     * @return callable|null
     */
    public function uploadPropertyContext(): ?\Closure
    {
        if (!$this->uploadEnabled() || $this->currentContext) {
            return null;
        }

        $this->currentContext = static::INPUT_UPLOAD;

        $baseInputId   = $this->inputId();
        $uploadInputId = $this->uploadInputId();

        return function ($template, LambdaHelper $helper) use ($baseInputId, $uploadInputId) {
            $this->setInputId($uploadInputId);
            $template = $helper->render($template);
            $this->setInputId($baseInputId);

            $this->currentContext = null;

            return $template;
        };
    }

    /**
     * Retrieve the elFinder connector URL template for rendering.
     *
     * This method is overriden to change the `callback` value to reflect
     * the correct input control ID.
     */
    #[\Override]
    protected function getFilePickerUrlTemplate(): string
    {
        $uri = 'obj_type={{ objType }}&obj_id={{ objId }}&property={{ p.ident }}&callback={{ uploadInputId }}';

        return '{{# withAdminUrl }}elfinder?' . $uri . '{{/ withAdminUrl }}';
    }

    /**
     * Retrieve the control's data options for JavaScript components.
     */
    #[\Override]
    public function controlDataForJs(): array
    {
        $this->inputId();
        $data    = parent::controlDataForJs();

        return array_replace($data, [
            // Audio Control
            'active_pane'         => $this->resolveActivePane(),
            'text_input_id'       => $this->textInputId(),
            'capture_input_id'    => $this->captureInputId(),
            'upload_input_id'     => $this->uploadInputId(),
            'hidden_input_id'     => $this->hiddenInputId(),
            'recorder_plugin_url' => $this->recorderPluginUrl(),
        ]);
    }
}
