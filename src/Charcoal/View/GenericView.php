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
     * ## Optional parameters:
     * - `logger` a PSR-3 logger
     * - `config` a ViewConfig object
     *
     * @param array $data View class dependencies.
     * @throws InvalidArgumentException If required parameters are missing.
     */
    public function __construct(array $data)
    {
        if (isset($data['logger'])) {
            $data['logger'] = new \Psr\Log\NullLogger();
        }
        $this->setLogger($data['logger']);

        if (isset($data['config'])) {
            $this->setConfig($data['config']);
        }
    }
}
