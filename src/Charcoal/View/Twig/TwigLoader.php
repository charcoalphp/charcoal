<?php

namespace Charcoal\View\Twig;

// From Twig
use Twig\Loader\LoaderInterface as TwigLoaderInterface;
use Twig\Loader\ExistsLoaderInterface as TwigExistsLoaderInterface;
use Twig\Loader\SourceContextLoaderInterface as TwigSourceContextLoaderInterface;
use Twig\Source as TwigSource;

// From 'charcoal-view'
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * Twig Template Loader
 *
 * Finds a Twig template file in a collection of directory paths.
 */
class TwigLoader extends AbstractLoader implements
    LoaderInterface,
    TwigLoaderInterface,
    TwigExistsLoaderInterface,
    TwigSourceContextLoaderInterface
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
    protected function isTemplateString($ident)
    {
        return strpos($ident, '{%') !== false || parent::isTemplateString($ident);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param  string $ident The identifier to convert.
     * @return string
     */
    protected function filenameFromIdent($ident)
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @see TwigLoaderInterface::getSource()
     *     Deprecated since Twig v1.27 (to be removed in Twig v2.0)
     *
     * @param  string $name The name of the template to load.
     * @return string The template source code.
     */
    public function getSource($name)
    {
        return $this->load($name);
    }

    /**
     * Returns the source context for a given template logical name.
     *
     * @see TwigSourceContextLoaderInterface::getSourceContext()
     *     Deprecated since Twig v1.27 (to be removed in Twig v3.0).
     *
     * @param  string $name The name of the template to load.
     * @return TwigSource The template source object.
     */
    public function getSourceContext($name)
    {
        $source = $this->load($name);
        return new TwigSource($source, $name);
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @see TwigExistsLoaderInterface::exists()
     *     Deprecated since Twig v1.12 (to be removed in Twig v3.0).
     *
     * @param  string $name The name of the template to load.
     * @return boolean
     */
    public function exists($name)
    {
        return !!$this->findTemplateFile($name);
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @see TwigLoaderInterface::getCacheKey()
     *
     * @param  string $name The name of the template to load.
     * @return string|null The cache key
     */
    public function getCacheKey($name)
    {
        $key = $this->findTemplateFile($name);
        return $key;
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
    public function isFresh($name, $time)
    {
        $file = $this->findTemplateFile($name);
        $fresh = (filemtime($file) <= $time);
        return $fresh;
    }
}
