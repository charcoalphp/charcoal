<?php

namespace Charcoal\Admin\Action\Elfinder;

use elFinderConnector;

/**
 * An elFinder connector that captures its output instead of writing it
 * directly to the response with `header()` and `exit`.
 *
 * The captured output is thrown as an {@see OutputException}, which the
 * {@see \Charcoal\Admin\Action\ElfinderConnectorAction} converts into a
 * PSR-7 response.
 */
class BufferedConnector extends elFinderConnector
{
    /**
     * Capture the connector output.
     *
     * @param  array $data The output data.
     * @return void
     * @throws OutputException Always.
     */
    protected function output(array $data)
    {
        $this->elFinder->getSession()->close();
        ignore_user_abort(false);

        throw new OutputException($data, $this->header, static::$contentType);
    }
}
