<?php

namespace Charcoal\View\Engine;

use \Charcoal\View\Mustache\MustacheEngine;
use \Charcoal\View\Php\PhpLoader;

/**
 * Mustache Engine with a PHP loader
 */
class PhpMustacheEngine extends MustacheEngine
{
    /**
     * @return string
     */
    public function type()
    {
        return 'php-mustache';
    }
}
