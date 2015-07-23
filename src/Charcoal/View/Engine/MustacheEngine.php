<?php

namespace Charcoal\View\Engine;

/**
*
*/
class MustacheEngine extends AbstractViewEngine
{
    /**
    * @return string
    */
    public function type()
    {
        return 'mustache';
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
        return 'mustache';
    }
}
