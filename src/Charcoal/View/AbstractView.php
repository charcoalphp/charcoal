<?php

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
     * @throws InvalidArgumentException If the template ident is not a string.
     * @return string
     */
    public function loadTemplate($templateIdent)
    {
        if (!is_string($templateIdent)) {
            throw new InvalidArgumentException(sprintf(
                'Template ident must be a string, received %s',
                is_object($templateIdent) ? get_class($templateIdent) : gettype($templateIdent)
            ));
        }
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
    public function render($templateIdent, $context = null)
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
    public function renderTemplate($templateString, $context = null)
    {
        return $this->engine()->render($templateString, $context);
    }

    /**
     * @param string      $varName       The name of the variable to set this template unto.
     * @param string|null $templateIdent The "dynamic template" to set. null to clear.
     * @return void
     */
    public function setDynamicTemplate($varName, $templateIdent)
    {
        $this->engine()->setDynamicTemplate($varName, $templateIdent);
    }

    /**
     * Get the view's rendering engine instance.
     *
     * @return EngineInterface
     */
    protected function engine()
    {
        return $this->engine;
    }

    /**
     * Set the engine (`EngineInterface`) dependency.
     *
     * @param EngineInterface $engine The rendering engine.
     * @return void
     */
    private function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }
}
