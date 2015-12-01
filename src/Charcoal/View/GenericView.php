<?php

namespace Charcoal\View;

/**
 * Concrete implementation of a _View_ interface (extends `AbstractView`).
 *
 */
class GenericView extends AbstractView
{
    /**
     * Build the object with an array of dependencies.
     *
     * ## Required paramaters:
     * - `logger` a PSR-3 logger
     *
     * ## Optional parameters:
     * - `config` a ViewConfig object
     *
     * @param array $data
     * @throws InvalidArgumentException If required parameters are missing.
     */
    public function __construct($data)
    {
        if (isset($data['config'])) {
            $this->set_config($data['config']);
        }
        $this->set_logger($data['logger']);

    }
}
