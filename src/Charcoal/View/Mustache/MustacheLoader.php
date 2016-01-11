<?php

namespace Charcoal\View\Mustache;

use \InvalidArgumentException;

// 3rd-party libraries (`mustache/mustache`) dependencies
use \Mustache_Loader;

// Parent namespace dependencies
use \Charcoal\View\AbstractLoader;
use \Charcoal\View\LoaderInterface;

/**
 * - The mustache template loader finds a mustache template file in directories.
 */
class MustacheLoader extends AbstractLoader implements
    Mustache_Loader,
    LoaderInterface
{
    /**
     * @see AbstractLoader::load()
     * @param string $ident The template identifier to load.
     * @throws InvalidArgumentException If the template ident parameter is not a string.
     * @return string
     */
    public function load($ident)
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
            // Error
            return '';
        }

        // $ident = $this->classname_to_ident($ident);
        $filename = $this->filename_from_ident($ident);
        $search_path = $this->paths();
        foreach ($search_path as $path) {
            $f = realpath($path).'/'.strtolower($filename);
            if (!file_exists($f)) {
                continue;
            }

            $this->logger->debug('Found matching template: '.$f);

            $file_content = file_get_contents($f);
            if ($file_content !== '') {
                return $file_content;
            }
        }

        $this->logger->debug(
            sprintf(
                'No matching templates found for "%1$s": %2$s',
                $ident,
                $filename
            ),
            $search_path
        );

        return $ident;
    }

    /**
     * Convert an identifier to a file path.
     *
     * @param string $ident The identifier to convert.
     * @return string
     */
    public function filename_from_ident($ident)
    {
        $filename  = str_replace([ '\\' ], '.', $ident);
        $filename .= '.mustache';

        return $filename;
    }

    /**
     * Convert a FQN to an identifier.
     *
     * @param string $classname The FQN to convert.
     * @return string
     */
    public function classname_to_ident($classname)
    {
        $ident = str_replace('\\', '/', strtolower($classname));
        $ident = ltrim($ident, '/');
        return $ident;
    }
}
