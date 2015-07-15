<?php

namespace Charcoal\View;

interface ViewEngineInterface
{
    /**
    * @return string
    */
    public function type();

    /**
    * @param string $filename
    * @return boolean Success / Failure
    */
    public function process_file($filename);

    /**
    * @return string
    */
    public function filename_extension();
}
