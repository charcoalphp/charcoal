<?php

namespace Charcoal\View\Php;

// PHP Dependencies
use \InvalidArgumentException;

// Parent namespace dependencies
use \Charcoal\View\AbstractLoader;
use \Charcoal\View\LoaderInterface;

/**
 * The PHP template loader finds a mustache php template file in directories and includes it (run as PHP).
 */
class PhpLoader extends AbstractLoader implements LoaderInterface
{
    /**
     * AbstractLoader > load()
     *
     * @param string $ident
     * @throws InvalidArgumentException If the template ident is not a string.
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
        $search_path = $this->search_path();
        foreach ($search_path as $path) {
            $f = realpath($path).'/'.$filename;
            if (!file_exists($f)) {
                continue;
            }

            $this->logger()->debug('Found matching template: '.$f);

            ob_start();
            include $f;
            $file_content = ob_get_clean();

            if ($file_content !== '') {
                return $file_content;
            }
        }

        $this->logger()->debug(
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
    private function filename_from_ident($ident)
    {
        $filename = str_replace([ '\\' ], '.', $ident);
        $filename .= '.php';

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
