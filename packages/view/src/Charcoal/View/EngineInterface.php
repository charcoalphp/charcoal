<?php

declare(strict_types=1);

namespace Charcoal\View;

/**
 * _Engines_ are the actual template renderers for the views.
 */
interface EngineInterface
{
    /**
     * Load a template (from identifier).
     *
     * @param string $templateIdent The template identifier to load.
     * @return string
     */
    public function loadTemplate(string $templateIdent): string;

    /**
     * Load a template (from identifier) and render it.
     *
     * @param string $templateIdent The template identifier to load and render.
     * @param mixed  $context       The rendering context.
     * @return string The rendered template string.
     */
    public function render(string $templateIdent, $context): string;

    /**
     * Render a template (from string).
     *
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate(string $templateString, $context): string;

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void;
}
