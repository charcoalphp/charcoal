<?php

namespace Charcoal\Admin\Template;

use ArrayIterator;
use RuntimeException;
use InvalidArgumentException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
// From Pimple
use Pimple\Container;
// From Mustache
use Mustache\LambdaHelper as LambdaHelper;
// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-property'
use Charcoal\Property\FileProperty;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;

/**
 *
 */
class ElfinderTemplate extends AdminTemplate
{
    public const ADMIN_ASSETS_REL_PATH    = 'assets/admin/';
    public const ELFINDER_ASSETS_REL_PATH = 'assets/admin/elfinder/';

    /**
     * Store the elFinder configuration from the admin configuration.
     *
     * @var \Charcoal\Config\ConfigInterface
     */
    protected $elfinderConfig;

    /**
     * Store the current property instance for the current class.
     *
     * @var \Charcoal\Property\PropertyInterface
     */
    protected $formProperty;

    /**
     * The related object type.
     */
    private string|bool|null $objType = null;

    /**
     * The related object ID.
     */
    private string|bool|null $objId = null;

    /**
     * The related property identifier.
     */
    private string|bool|null $propertyIdent = null;

    /**
     * Whether to output JS/CSS assets for initializing elFinder.
     */
    private bool $showAssets = true;

    /**
     * Custom localization messages.
     *
     * @var array<string, (Translation|string|null)>|null
     */
    private \ArrayIterator|array|null $localizations = null;

    /**
     * The related JS callback ID.
     */
    private string|bool $callbackIdent = '';

    /**
     * URL for the elFinder connector.
     *
     * @var string
     */
    private $elfinderConnectorUrl;

    /**
     * Sets the template data from a PSR Request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     */
    #[\Override]
    protected function setDataFromRequest(RequestInterface $request): static
    {
        $keys = $this->validDataFromRequest();
        $data = $request->getParams($keys);

        if (isset($data['obj_type'])) {
            $this->objType = htmlspecialchars(trim($data['obj_type']), ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['obj_id'])) {
            $this->objId = htmlspecialchars(trim($data['obj_id']), ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['property'])) {
            $this->propertyIdent = htmlspecialchars(trim($data['property']), ENT_QUOTES, 'UTF-8');
        }

        if (isset($data['assets'])) {
            $this->showAssets = (bool)$data['assets'];
        }

        if (isset($data['callback'])) {
            $this->callbackIdent = htmlspecialchars(trim($data['callback']), ENT_QUOTES, 'UTF-8');
        }

        if (isset($this->elfinderConfig['translations'])) {
            $this->setLocalizations(array_replace_recursive(
                $this->defaultLocalizations(),
                $this->elfinderConfig['translations']
            ));
        }

        return true;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    #[\Override]
    protected function validDataFromRequest(): array
    {
        return array_merge([
            // Current object
            'obj_type', 'obj_id', 'property',
            // elFinder instance
            'assets', 'callback'
        ], parent::validDataFromRequest());
    }

    /**
     * Retrieve the title of the page.
     *
     * @return Translation
     */
    #[\Override]
    public function title()
    {
        if ($this->title === null) {
            $this->title = $this->translator()->translation('filesystem.library.media');
        }

        return $this->title;
    }

    /**
     * Set the custom localization messages.
     *
     * @param  array $localizations An associative array of localizations.
     */
    public function setLocalizations(array $localizations): static
    {
        $this->localizations = new ArrayIterator();

        foreach ($localizations as $ident => $translations) {
            $this->addLocalization($ident, $translations);
        }

        return $this;
    }

    /**
     * Add a custom localization message.
     *
     * @param  string $ident        The message ID.
     * @param  mixed  $translations The message translations.
     * @throws InvalidArgumentException If the message ID is not a string or the translations are invalid.
     */
    public function addLocalization($ident, $translations): static
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (get_debug_type($ident))
            ));
        }

        $this->localizations[$ident] = $this->translator()->translation($translations);

        return $this;
    }

    /**
     * Remove the translations for the given message ID.
     *
     * @param  string $ident The message ID to remove.
     * @throws InvalidArgumentException If the message ID is not a string.
     */
    public function removeLocalization($ident): static
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (get_debug_type($ident))
            ));
        }

        unset($this->localizations[$ident]);

        return $this;
    }

    /**
     * Count the number of localizations.
     */
    public function numLocalizations(): int
    {
        return count($this->localizations());
    }

    /**
     * Determine if there are any localizations.
     */
    public function hasLocalizations(): bool
    {
        return (bool)$this->numLocalizations();
    }

    /**
     * Retrieve the localizations.
     *
     * @return array<string, (Translation|string|null)>
     */
    public function localizations(): \ArrayIterator|array|null
    {
        if ($this->localizations === null) {
            $this->setLocalizations($this->defaultLocalizations());
        }

        return $this->localizations;
    }

    /**
     * Retrieve the translations for the given message ID.
     *
     * @param  string $ident The message ID to lookup.
     * @throws InvalidArgumentException If the message ID is not a string.
     * @return Translation|string|null
     */
    public function localization($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Translation key must be a string, received %s',
                (get_debug_type($ident))
            ));
        }

        return ($this->localizations[$ident] ?? $ident);
    }

    /**
     * Retrieve the custom localizations for elFinder.
     *
     * @return array<string, array<string, (string|null)>>
     */
    public function elfinderLocalizations(): array
    {
        $i18n = [];

        foreach ($this->localizations() as $id => $translations) {
            if ($translations instanceof Translation) {
                foreach ($translations->data() as $language => $message) {
                    $i18n[$language][$id] = $message;
                }
            } else {
                $i18n[$language][$id] = $translations;
            }
        }

        return $i18n;
    }

    /**
     * Converts the elFinder {@see self::elfinderLocalizations() localizations} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function elfinderLocalizationsAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->elfinderLocalizations(), $options);
    }

    /**
     * Converts the elFinder {@see self::elfinderLocalizations() localizations} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    final public function escapedElfinderLocalizationsAsJson(): string
    {
        return '{{=<% %>=}}' . $this->elfinderLocalizationsAsJson() . '<%={{ }}=%>';
    }

    /**
     * @return string
     */
    public function adminAssetsUrl()
    {
        return $this->baseUrl(static::ADMIN_ASSETS_REL_PATH);
    }

    /**
     * @return string
     */
    public function elfinderAssetsUrl()
    {
        return $this->baseUrl(static::ELFINDER_ASSETS_REL_PATH);
    }

    public function elfinderAssets(): bool
    {
        return $this->showAssets;
    }

    /**
     * Retrieve the current elFinder callback ID from the GET parameters.
     *
     * @return string|null
     */
    public function elfinderCallback(): string|bool
    {
        return $this->callbackIdent;
    }

    /**
     * @param  string $url The elFinder connector AJAX URL.
     */
    public function setElfinderConnectorUrl($url): static
    {
        $this->elfinderConnectorUrl = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function elfinderConnectorUrl()
    {
        return $this->elfinderConnectorUrl;
    }

    /**
     * Render the elFinder connector URL with the correct object model context.
     *
     * This method (a necessary evil) allows one to customize the URL
     * without duplicating the template view.
     *
     * @see \Charcoal\Admin\Property\Input\FileInput::prepareFilePickerUrl()
     *
     * @return callable|null
     */
    public function prepareElfinderConnectorUrl()
    {
        $uri = $this->getElfinderConnectorUrlTemplate();

        return function ($noop, LambdaHelper $helper) use ($uri): null {
            $uri = $helper->render($uri);
            $this->setElfinderConnectorUrl($uri);

            return null;
        };
    }

    /**
     * Retrieve the elFinder connector URL template for rendering.
     */
    protected function getElfinderConnectorUrlTemplate(): string
    {
        $uri = 'obj_type={{ objType }}&obj_id={{ objId }}&property={{ propertyIdent }}';

        return '{{# withAdminUrl }}elfinder-connector?' . $uri . '{{/ withAdminUrl }}';
    }

    /**
     * Retrieve the current object type from the GET parameters.
     *
     * @return string|null
     */
    public function objType(): string|bool|null
    {
        return $this->objType;
    }

    /**
     * Retrieve the current object ID from the GET parameters.
     *
     * @return string|null
     */
    public function objId(): string|bool|null
    {
        return $this->objId;
    }

    /**
     * Retrieve the current object's property identifier from the GET parameters.
     *
     * @return string|null
     */
    public function propertyIdent(): string|bool|null
    {
        return $this->propertyIdent;
    }

    /**
     * Retrieve the current property.
     *
     * @return \Charcoal\Property\PropertyInterface|boolean A Form Property instance
     *     or FALSE if a property can not be resolved.
     */
    public function formProperty()
    {
        if ($this->formProperty === null) {
            $this->formProperty = false;

            $objType       = $this->objType();
            $propertyIdent = $this->propertyIdent();

            if ($objType && $propertyIdent) {
                $model = $this->modelFactory()->get($objType);

                if ($model->hasProperty($propertyIdent)) {
                    $this->formProperty = $model->property($propertyIdent);
                }
            }
        }

        return $this->formProperty;
    }

    /**
     * Retrieve the elFinder client-side settings.
     *
     * @return array
     */
    public function elfinderClientConfig()
    {
        $settings = empty($this->elfinderConfig['client']) ? [] : $this->elfinderConfig['client'];

        $settings['lang'] = $this->translator()->getLocale();

        $property = $this->formProperty();
        if ($property) {
            $mimeTypes = htmlspecialchars(trim(($_GET['filetype'] ?? '')), ENT_QUOTES, 'UTF-8');

            if ($mimeTypes) {
                if ($mimeTypes === 'file') {
                    $mimeTypes = [];
                } elseif (!is_array($mimeTypes)) {
                    $mimeTypes = explode(',', $mimeTypes);
                    $mimeTypes = array_filter($mimeTypes, strlen(...));
                }

                $settings['onlyMimes'] = $mimeTypes;
            } elseif ($property instanceof FileProperty) {
                $settings['onlyMimes'] = $property['acceptedMimetypes'];
            }

            $settings['rememberLastDir'] = !($property instanceof FileProperty);
        }

        return $settings;
    }

    /**
     * Converts the elFinder client-side {@see self::elfinderClientConfig() options} as a JSON string.
     *
     * @return string Returns data serialized with {@see json_encode()}.
     */
    public function elfinderClientConfigAsJson()
    {
        $options = (JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($this->debug()) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($this->elfinderClientConfig(), $options);
    }

    /**
     * Converts the elFinder client-side {@see self::elfinderClientConfig() options} as a JSON string, protected from Mustache.
     *
     * @return string Returns a stringified JSON object, protected from Mustache rendering.
     */
    final public function escapedElfinderClientConfigAsJson(): string
    {
        return '{{=<% %>=}}' . $this->elfinderClientConfigAsJson() . '<%={{ }}=%>';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->elfinderConfig = $container['elfinder/config'];
    }

    /**
     * Retrieve the default custom localizations.
     *
     * @return array<string, (Translation|string|null)>
     */
    protected function defaultLocalizations(): array
    {
        $t = $this->translator();

        return [
            'volume_default' => $t->translation('filesystem.volume.default'),
            'volume_library' => $t->translation('filesystem.volume.library'),
            'volume_storage' => $t->translation('filesystem.volume.storage'),
            'volume_uploads' => $t->translation('filesystem.volume.uploads'),
            'volume_public'  => $t->translation('filesystem.volume.public'),
            'volume_private' => $t->translation('filesystem.volume.private'),
        ];
    }



    // Templating
    // =========================================================================
    /**
     * Determine if main & secondary menu should appear as mobile in a desktop resolution.
     */
    #[\Override]
    public function isFullscreenTemplate(): bool
    {
        return false;
    }
}
