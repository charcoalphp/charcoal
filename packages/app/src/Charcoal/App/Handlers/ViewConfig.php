<?php

namespace Charcoal\App\Handlers;

use Charcoal\Config\AbstractConfig;

class ViewConfig extends AbstractConfig
{
    /**
     * The view controller.
     */
    protected string $view;

    /**
     * The type of engine (mustache, twig, etc.)
     */
    protected string $engine;

    /**
     * The template identifier.
     */
    protected ?string $template;
}
