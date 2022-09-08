<?php

declare(strict_types=1);

namespace Charcoal\View;

/**
 * Defines an object as viewable, and therefore can be rendered.
 */
interface ViewableInterface
{
    /**
     * Render the viewable object.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Set the viewable object's template identifier.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string $templateIdent The template ID.
     * @return self
     */
    public function setTemplateIdent(string $templateIdent);

    /**
     * Retrieve the viewable object's template identifier.
     *
     * @return string
     */
    public function templateIdent(): ?string;

    /**
     * Set the renderable view.
     *
     * @param ViewInterface $view The view instance to use to render.
     * @return self
     */
    public function setView(ViewInterface $view);

    /**
     * Retrieve the renderable view.
     *
     * @return ViewInterface|null The object's View instance.
     */
    public function view(): ?ViewInterface;

    /**
     * Render the template by the given identifier.
     *
     * Usually, a path to a file containing the template to be rendered at runtime.
     *
     * @param string|null $templateIdent The template to load, parse, and render.
     *     If NULL, will use the object's previously set template identifier.
     * @return string The rendered template.
     */
    public function render(?string $templateIdent = null): string;

    /**
     * Render the given template from string.
     *
     * @param string $templateString The template  to render from string.
     * @return string The rendered template.
     */
    public function renderTemplate(string $templateString): string;

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void;
}
