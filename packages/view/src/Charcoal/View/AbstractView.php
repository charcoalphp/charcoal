<?php

declare(strict_types=1);

namespace Charcoal\View;

use Exception;
use InvalidArgumentException;
// From 'charcoal-view'
use Charcoal\View\EngineInterface;
use Charcoal\View\ViewInterface;

/**
 * Base abstract class for _View_ interfaces, implements `ViewInterface`.
 *
 * Also implements the `ConfigurableInterface`
 */
abstract class AbstractView implements ViewInterface
{
    /**
     * @var EngineInterface $engine
     */
    private $engine;

    /**
     * Build the object with an array of dependencies.
     *
     * @param array $data View class dependencies.
     * @throws InvalidArgumentException If required parameters are missing.
     */
    public function __construct(array $data)
    {
        $this->setEngine($data['engine']);
    }

    /**
     * Load a template (from identifier).
     *
     * @param string $templateIdent The template identifier to load..
     * @return string
     */
    public function loadTemplate(string $templateIdent): string
    {
        if (!$templateIdent) {
            return '';
        }
        return $this->engine()->loadTemplate($templateIdent);
    }

    /**
     * Load a template (from identifier) and render it.
     *
     * @param string $templateIdent The template identifier, to load and render.
     * @param mixed  $context       The view controller (rendering context).
     * @return string
     */
    public function render(string $templateIdent, $context = null): string
    {
        return $this->engine()->render($templateIdent, $context);
    }

    /**
     * Render a template (from string).
     *
     * @param string $templateString The full template string to render.
     * @param mixed  $context        The view controller (rendering context).
     * @return string
     */
    public function renderTemplate(string $templateString, $context = null): string
    {
        return $this->engine()->render($templateString, $context);
    }

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate(string $varName, ?string $templateIdent): void
    {
        $this->engine()->setDynamicTemplate($varName, $templateIdent);
    }

    /**
     * Get the view's rendering engine instance.
     *
     * @return EngineInterface
     */
    protected function engine(): EngineInterface
    {
        return $this->engine;
    }

    /**
     * Set the engine (`EngineInterface`) dependency.
     *
     * @param EngineInterface $engine The rendering engine.
     * @return void
     */
    private function setEngine(EngineInterface $engine): void
    {
        $this->engine = $engine;
    }
}
