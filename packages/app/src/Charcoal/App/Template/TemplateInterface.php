<?php

namespace Charcoal\App\Template;

use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
interface TemplateInterface
{
    /**
     * @param array $data The template data to set.
     * @return TemplateInterface Chainable
     */
    public function setData(array $data);

    /**
     * Initialize the template with a request.
     *
     * @param ServerRequestInterface $request The request to intialize.
     * @return boolean
     */
    public function init(ServerRequestInterface $request);
}
