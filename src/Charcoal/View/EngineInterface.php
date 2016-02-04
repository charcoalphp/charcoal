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
     * @return MustacheEngine Chainable
     */
    public function setLoader(LoaderInterface $loader);

    /**
     * @param string $templateIdent
     * @return string
     */
    public function loadTemplate($templateIdent);

    /**
     * @param string $templateIdent
     * @param mixed  $context
     * @return string
     */
    public function render($templateIdent, $context);

    /**
     * @param string $templateIdent
     * @param mixed  $context
     * @return string
     */
    public function renderTemplate($templateString, $context);
}
