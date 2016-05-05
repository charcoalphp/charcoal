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
     * @throws Exception If the target template file is empty.
     * @return string
     */
    public function load($ident)
    {
        $file = $this->findTemplateFile($ident);

        $file_content = file_get_contents($file);
        if ($file_content == '') {
            throw new Exception(
                sprintf('Can not load template %s (empty file)', $ident)
            );
        }

        return $file_content;
    }

    /**
     * @param string $ident The template identifier to load.
     * @throws InvalidArgumentException If the ident parameter is not a string or is an invalid template.
     * @throws Exception If no template could be found.
     * @return sring The matching template file name (full path).
     */
    public function findTemplateFile($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Template ident must be a string'
            );
        }

        // Handle dynamic template hack.
        if ($ident === '$widgetTemplate') {
            $ident = (isset($GLOBALS['widgetTemplate']) ? $GLOBALS['widgetTemplate'] : null);
            if (!is_string($ident)) {
                throw new InvalidArgumentException(
                    'Can not find template (invalid $widgetTemplate).'
                );
            }
        }

        $filename = $this->filenameFromIdent($ident);
        $search_path = $this->paths();
        foreach ($search_path as $path) {
            $f = realpath($path).'/'.$filename;
            if (file_exists($f)) {
                $this->logger->debug('Found matching template: '.$f);
                return $f;
            }
        }

        $log = sprintf(
            'No matching templates found for "%1$s": %2$s',
            $ident,
            $filename
        );

        $this->logger->debug($log, $search_path);

        throw new Exception($log);
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    public function filenameFromIdent($ident)
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.twig';

        return $filename;
    }

    /**
     * Convert a FQN to an identifier.
     *
     * @param string $classname The FQN to convert.
     * @return string
     */
    public function classnameToIdent($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }

    /**
     * Twig_LoaderInterface > getSource()
     *
     * Gets the source code of a template, given its name.
     *
     * @param  string $name The name of the template to load.
     * @return string The template source code.
     */
    public function getSource($name)
    {
        $source = $this->load($name);
        return $source;
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
