<?php

declare(strict_types=1);

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
     * @return string
     */
    public function loadTemplate(string $templateIdent): string;

    /**
     * Load a template (from identifier) and render it.
     *
     * @param string $templateIdent The template identifier, to load and render.
     * @param mixed  $context       The view controller (rendering context).
     * @return string
     */
    public function render(string $templateIdent, $context = null): string;

    /**
     * Render a template (from string).
     *
     * @param string $templateString The full template string to render.
     * @param mixed  $context        The view controller (rendering context).
     * @return string
     */
    public function renderTemplate(string $templateString, $context = null);

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void;
}
