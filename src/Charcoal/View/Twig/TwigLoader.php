<?php

namespace Charcoal\View\Twig;

use Exception;
use InvalidArgumentException;

// From `twig/twig`
use Twig_LoaderInterface;
use Twig_Source;

// Parent namespace dependencies
use Charcoal\View\AbstractLoader;
use Charcoal\View\LoaderInterface;

/**
 * The Charcoal View Twig Loader implements both Twig_LoaderInterface and its own LoaderInterface.
 */
class TwigLoader extends AbstractLoader implements
    LoaderInterface,
    Twig_LoaderInterface
{

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    protected function filenameFromIdent($ident)
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }

    /**
     * Twig_LoaderInterface > getSource()
     *
     * Gets the source code of a template, given its name.
     *
     * @param  string $name The name of the template to load.
     * @return string The template source code.
     */
    public function getSourceContext($name)
    {
        $source = $this->load($name);
        return new Twig_Source($source, $name);
    }

    /**
     * Twig_LoaderInterface > exists()
     *
     * @param  string $name The name of the template to load.
     * @return bool
     */
    public function exists($name)
    {
        return !!findTemplateFile($name);
    }

    /**
     * Twig_LoaderInterface > getCacheKey()
     *
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name The name of the template to load.
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        $key = $this->findTemplateFile($name);
        return $key;
    }

    /**
     * Twig_LoaderInterface > isFresh()
     *
     * Returns true if the template is still fresh.
     *
     * @param string  $name The template name.
     * @param integer $time The last modification time of the cached template.
     * @return boolean
     */
    public function isFresh($name, $time)
    {
        $file = $this->findTemplateFile($name);
        $fresh = (filemtime($file) <= $time);
        return $fresh;
    }
}
