<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;
use Twig\Source as TwigSource;

/**
 * Twig Template Loader
 *
 * Finds a Twig template file in a collection of directory paths.
 */
class TwigLoader extends AbstractLoader implements
    LoaderInterface,
    TwigLoaderInterface
{
    /**
     * Determine if the variable is a template literal.
     *
     * This method looks for any tag delimiters in the given string,
     * which a file path would most likely not have.
     *
     * @todo   Add support for custom delimiters.
     * @param  string $ident The template being evaluated.
     * @return boolean
     */
    protected function isTemplateString(string $ident): bool
    {
        return strpos($ident, '{%') !== false || parent::isTemplateString($ident);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param  string $ident The identifier to convert.
     * @return string
     */
    protected function filenameFromIdent(string $ident): string
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }

    /**
     * Returns the source context for a given template logical name.
     *
     * @see Twig\Loader\LoaderInterface::getSourceContext()
     *
     * @param  string $name The name of the template to load.
     * @return TwigSource The template source object.
     */
    public function getSourceContext(string $name): TwigSource
    {
        $source = $this->load($name);
        return new TwigSource($source, $name);
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @see Twig\Loader\LoaderInterface::exists()
     *
     * @param  string $name The name of the template to load.
     * @return boolean
     */
    public function exists(string $name): bool
    {
        return !!$this->findTemplateFile($name);
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @see TwigLoaderInterface::getCacheKey()
     *
     * @param  string $name The name of the template to load.
     * @return string The cache key
     */
    public function getCacheKey(string $name): string
    {
        if (($path = $this->findTemplateFile($name)) === null) {
            return '';
        }

        $len = \strlen($this->basePath());
        if (0 === strncmp($this->basePath(), $path, $len)) {
            return substr($path, $len);
        }

        return $path;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @see TwigLoaderInterface::isFresh()
     *
     * @param  string  $name The template name.
     * @param  integer $time The last modification time of the cached template.
     * @return boolean
     */
    public function isFresh(string $name, int $time): bool
    {
        $file = $this->findTemplateFile($name);
        $fresh = (filemtime($file) <= $time);
        return $fresh;
    }
}
