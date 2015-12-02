<?php

namespace Charcoal\View\Twig;

use \InvalidArgumentException;
use \Exception;

// From `twig/twig`
use \Twig_LoaderInterface;

// Parent namespace dependencies
use \Charcoal\View\AbstractLoader;
use \Charcoal\View\LoaderInterface;

/**
 *
 */
class TwigLoader extends AbstractLoader implements 
    LoaderInterface,
    Twig_LoaderInterface
{
    /**
     * AbstractLoader > load()
     *
     * @param string $ident The template identifier to load.
     * @return string
     */
    public function load($ident)
    {
        $f = $this->find_template($ident);
            
        $file_content = file_get_contents($f);
        if ($file_content == '') {
            throw new Exception(
                sprintf('Can not load template %s (empty file)', $ident)
            );
        }

        return $file_content;
    }

    /**
    * @throws InvalidArgumentException If the ident parameter is not a string or is an invalid template.
    * @throws Exception If no template could be found
    */
    public function find_template($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }

        // Handle dynamic template hack. @todo rename to $mustache_template
        if ($ident === '$widget_template') {
            $ident = (isset($GLOBALS['widget_template']) ? $GLOBALS['widget_template'] : null);
        }

        if ($ident === null) {
            throw new InvalidArgumentException(
                'Can not find template (invalid $widget_template).'
            );
        }

        $filename = $this->filename_from_ident($ident);
        $search_path = $this->paths();
        foreach ($search_path as $path) {
            $f = realpath($path).'/'.$filename;
            if (file_exists($f)) {
                return $f;
            }
        }

        throw new Exception(
            sprintf('Can not find template "%s" (%s).', $ident, $filename)
        );
    }

    /**
     * @param string $ident
     * @return string
     */
    public function filename_from_ident($ident)
    {
        $filename = str_replace(['\\'], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param  string $name string The name of the template to load
     * @return string The template source code
     */
    public function getSource($name)
    {
        $source = $this->load($name);
        var_dump($source);
        return $source;
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name string The name of the template to load
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        $key = $this->find_template($name);
        return $key;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     */
    public function isFresh($name, $time)
    {
        $fresh = filemtime($this->find_template($name)) <= $time;
        return $fresh;
    }
}
