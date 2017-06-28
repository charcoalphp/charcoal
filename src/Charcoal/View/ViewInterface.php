<?php

namespace Charcoal\View;

/**
 * View Interface
 */
interface ViewInterface
{
    /**
     * Load a template (from identifier).
     *
     * @param string $templateIdent The template identifier to load..
     * @throws InvalidArgumentException If the template ident is not a string.
     * @return string
     */
    public function loadTemplate($templateIdent);

    /**
     * Load a template (from identifier) and render it.
     *
     * @param string $templateIdent The template identifier, to load and render.
     * @param mixed  $context       The view controller (rendering context).
     * @return string
     */
    public function render($templateIdent, $context = null);

    /**
     * Render a template (from string).
     *
     * @param string $templateString The full template string to render.
     * @param mixed  $context        The view controller (rendering context).
     * @return string
     */
    public function renderTemplate($templateString, $context = null);

    /**
     * @param string $varName       The name of the variable to set this template unto.
     * @param string $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate($varName, $templateIdent);
}
