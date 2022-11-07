<?php

declare(strict_types=1);

namespace Charcoal\View;

use InvalidArgumentException;

/**
 * View Aggregator
 *
 * The aggregator is used to register several engines to dynamically resolve
 * which engine to render any given template path or contents.
 * Alternatively, you can manually decide which engine to use for a given scenario.
 */
class ViewAggregator extends AbstractView
{
    /**
     *  @var array<string, EngineInterface>
     */
    protected array $engines = [];


    /**
     *  @var callable(string, mixed, array<string, EngineInterface>, EngineInterface): EngineInterface
     */
    protected $renderTemplateStringDecider;

    /**
     *  @var callable(string, mixed, array<string, EngineInterface>, EngineInterface): EngineInterface
     */
    protected $renderTemplateFileDecider;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->setEngines($data['engines']);
        $this->renderTemplateFileDecider = $data['file_decider'];
        $this->renderTemplateStringDecider = $data['string_decider'];
    }

    /**
     * @param array<string, EngineInterface> $data
     * @return void
     */
    private function setEngines(array $engines): void
    {
        foreach ($engines as $ident => $engine) {
            if ($engine instanceof EngineInterface) {
                $this->engines[$ident] = $engine;
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Expected an instance of %s, received %s',
                    EngineInterface::class,
                    (is_object($engine) ? get_class($engine) : gettype($engine))
                ));
            }
        }
    }

    /**
     * @return array<string, EngineInterface>
     */
    private function getEngines(): array
    {
        return $this->engines;
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
        $engine = call_user_func($this->renderTemplateFileDecider, $templateIdent, $context, $this->engines, $this->engine());
        return $engine->render($templateIdent, $context);
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
        $engine = call_user_func($this->renderTemplateStringDecider, $templateString, $context, $this->engines, $this->engine());
        return $engine->renderTemplate($templateString, $context);
    }

    /**
     * Determine if the given engine is registered.
     *
     * @param string $ident The engine identifier.
     */
    public function has(string $ident): bool
    {
        return isset($this->engines[$ident]);
    }

    /**
     * Retrieve the given engine if registered.
     *
     * @param  string $ident The engine identifier.
     * @throws InvalidArgumentException If the identifier is not registered.
     * @return ViewInterface
     */
    public function get(string $ident): ViewInterface
    {
        if (!$this->has($ident)) {
            throw new InvalidArgumentException(
                sprintf('Engine [%s] not registered', $ident)
            );
        }

        return new  GenericView([
            'engine' => $this->engines[$ident],
        ]);
    }

    /**
     * Change the default engine.
     *
     * @param  string $ident The engine identifier.
     * @throws InvalidArgumentException If the identifier is not registered.
     * @return self
     */
    public function using(string $ident)
    {
        if (!$this->has($ident)) {
            throw new InvalidArgumentException(sprintf(
                'Engine [%s] not registered, must be one of %s',
                $ident,
                implode(', ', array_keys($this->engines))
            ));
        }

        $this->setEngine($this->engines[$ident]);
        return $this;
    }
}
