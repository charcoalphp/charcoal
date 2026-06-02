<?php

namespace Charcoal\Admin\Property\Input;

use RuntimeException;
// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\UriInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\TextInput;

/**
 * Permalink Property Form Control
 */
class PermalinkInput extends TextInput
{
    /**
     * The base URI.
     *
     * @var UriInterface|null
     */
    protected $baseUrl;

    /**
     * The base permalink URI.
     *
     * @var string|null
     */
    protected $baseRoute;

    /**
     * The permalink sample ID.
     *
     * @var string|null
     */
    protected $sampleId;

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setBaseUrl($container['base-url']);
    }

    /**
     * Set the input ID.
     *
     * Used for the HTML "ID" attribute.
     *
     * @param  string $inputId HTML input id attribute.
     */
    #[\Override]
    public function setInputId($inputId): static
    {
        parent::setInputId($inputId);
        $this->sampleId = $inputId;
        return $this;
    }

    /**
     * Get the permalink's absolute URI.
     */
    public function viewLink(): string
    {
        $link   = null;
        $locale = $this->lang();
        if ($locale !== null) {
            $translator = $this->translator();
            $origLocale = $translator->getLocale();
            $translator->setLocale($this->lang());
            $link = $this->renderTemplate('{{# withBaseUrl }}{{ obj.url }}{{/ withBaseUrl }}');
            $translator->setLocale($origLocale);
        }

        return $link;
    }

    /**
     * Set the permalink's immutable base.
     *
     * @param  mixed $route The base URI.
     */
    protected function setBaseRoute($route): static
    {
        $this->baseRoute = $this->translator()->translation($route);
        return $this;
    }

    /**
     * Get the permalink's immutable base.
     */
    public function baseRoute(): string
    {
        if ($this->baseRoute === null) {
            $this->baseRoute = $this->baseUrl();
        }

        $link   = $this->baseRoute;
        $locale = $this->lang();
        if ($locale !== null) {
            $translator = $this->translator();
            $origLocale = $translator->getLocale();
            $translator->setLocale($this->lang());
            $link = $this->renderTemplate((string)$link);
            $translator->setLocale($origLocale);
        }

        return rtrim($link, '/') . '/';
    }

    /**
     * Get the permalink's editable part.
     *
     * @return string|null
     */
    public function editableRoute()
    {
        $link = $this->inputVal();
        if (in_array($link, [null, '', '0'], true)) {
            $link = $this->placeholder();
        }

        return $link;
    }

    /**
     * Set the base URI of the project.
     *
     * @param  UriInterface $uri The base URI.
     */
    protected function setBaseUrl(UriInterface $uri): static
    {
        $this->baseUrl = $uri;
        return $this;
    }

    /**
     * Get the base URI of the project.
     *
     * @throws RuntimeException If the base URI is missing.
     * @return UriInterface|null
     */
    public function baseUrl()
    {
        if ($this->baseUrl === null) {
            throw new RuntimeException(sprintf(
                'The base URI is not defined for [%s]',
                static::class
            ));
        }

        return $this->baseUrl;
    }

    /**
     * Iterate the samples to display.
     *
     * @return \Generator
     */
    public function samples()
    {
        if ($this->p()['l10n'] === false) {
            $origLang = $this->lang();
            $locales  = $this->translator()->availableLocales();
            $sampleId = $this->sampleId();
            foreach ($locales as $langCode) {
                $this->setSampleId($sampleId . '_' . $langCode);
                $this->setLang($langCode);

                yield $this;
            }
            $this->setSampleId($sampleId);
            $this->setLang($origLang);
        } else {
            yield $this;
        }
    }

    /**
     * Set the sample ID.
     *
     * Used for the HTML "ID" attribute.
     *
     * @param  string $id HTML sample ID attribute.
     */
    public function setSampleId($id): static
    {
        $this->sampleId = $id;
        return $this;
    }

    /**
     * Get the sample ID.
     *
     * If none was previously set then a unique random one will be generated.
     *
     * @return string
     */
    public function sampleId()
    {
        if (!$this->sampleId) {
            $this->sampleId = $this->inputId() ?: $this->generateSampleId();
        }

        return $this->sampleId;
    }

    /**
     * Generate a unique sample ID.
     */
    protected function generateSampleId(): string
    {
        return 'sample_' . uniqid();
    }
}
