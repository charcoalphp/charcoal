<?php

namespace Charcoal\Admin\Property\Display;

use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From 'charcoal-property'
use Charcoal\Property\FileProperty;
use Charcoal\Property\ImageProperty;
// From 'charcoal-translator'
use Charcoal\Translator\Translation;
// From 'charcoal-admin'
use Charcoal\Admin\Property\AbstractPropertyDisplay;
use Charcoal\Admin\Support\BaseUrlTrait;

/**
 * Link Display Property
 */
class LinkDisplay extends AbstractPropertyDisplay
{
    use BaseUrlTrait;

    /**
     * @var string|null
     */
    private $linkTextFormat;

    /**
     * @return string
     */
    #[\Override]
    public function displayVal()
    {
        $prop  = $this->property();
        $value = $this->propertyVal();

        if ($value instanceof Translation) {
            $value = $value->data();
        }

        if (!is_array($value)) {
            $value = [ $value ];
        }

        $links = [];
        foreach ($value as $key => $val) {
            if (empty($val)) {
                continue;
            }

            $links[$key] = $this->formatHtmlLink($val);
        }

        return $prop->displayVal($links);
    }

    /**
     * @return string[]|\Generator
     */
    public function displayValList()
    {
        $prop  = $this->property();
        $value = $this->propertyVal();

        if ($value instanceof Translation) {
            $value = $value->data();
        }

        if (!is_array($value)) {
            $value = [ $value ];
        }

        foreach ($value as $key => $val) {
            if (empty($val)) {
                continue;
            }

            $link = $this->formatHtmlLink($val);

            yield $key => $prop->displayVal($link);
        }
    }

    /**
     * Format the HTML link element.
     *
     * @param  string $url  The link URL.
     * @param  string $text The link text.
     */
    protected function formatHtmlLink($url, $text = null): string
    {
        if ($text === null) {
            $text = $url;
        }

        $format = $this->getLinkTextFormat();
        if ($format) {
            $text = $format($text);
        }

        return sprintf(
            '<a href="%s">%s</a>',
            $this->getLocalUrl($url),
            $text
        );
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string $path The file path.
     * @return string
     */
    protected function getLocalUrl($path)
    {
        $prop = $this->property();
        if ($prop instanceof FileProperty && $prop['publicAccess'] === false) {
            $query = http_build_query([
                'disk' => $prop['filesystem'],
                'path' => $path,
            ]);
            return $this->adminUrl('filesystem/download')->withQuery($query);
        }

        return $this->baseUrl($path);
    }

    /**
     * @param  callable|string|null $format The link textt format.
     * @throws InvalidArgumentException If the format is not a valid callable.
     */
    public function setLinkTextFormat($format): static
    {
        if ($format !== null && !function_exists($format)) {
            throw new InvalidArgumentException(
                'Link text format must be a valid callable'
            );
        }

        $this->linkTextFormat = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getLinkTextFormat()
    {
        return $this->linkTextFormat;
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Satisfies BaseUrlTrait dependencies
        $this->setBaseUrl($container['base-url']);
        $this->setAdminUrl($container['admin/base-url']);
    }
}
