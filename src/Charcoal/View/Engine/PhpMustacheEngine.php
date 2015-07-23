<?php

namespace Charcoal\View\Engine;

/**
*
*/
class PhpMustacheEngine extends AbstractViewEngine
{
    /**
    * @return string
    */
    public function type()
    {
        return 'php-mustache';
    }

    /**
    * @param string $filename
    * @return boolean Success / Failure
    */
    public function process_file($filename)
    {
        return file_get_contents($filename);
    }

    /**
    * @return string
    */
    public function filename_extension()
    {
        return 'php';
    }
}
