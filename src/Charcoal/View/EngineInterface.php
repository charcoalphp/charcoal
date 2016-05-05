<?php

namespace Charcoal\View;

/**
 * _Engines_ are the actual template renderers for the views.
 *
 */
interface EngineInterface
{
    /**
     * @param LoaderInterface $loader A loader instance.
     * @return EngineInterface Chainable
     */
    public function setLoader(LoaderInterface $loader);

    /**
     * @param string $templateIdent The template identifier to load.
     * @return string
     */
    public function loadTemplate($templateIdent);

    /**
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render($templateIdent, $context);

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate($templateString, $context);
}
