<?php

declare(strict_types=1);

namespace Charcoal\View;

use InvalidArgumentException;

class ViewAggregator extends AbstractView
{
    /**
     *  @var array<string, EngineInterface>
     */
    protected array $engines = [];

    protected EngineInterface $engine;

    /**
     *  @var callable
     */
    protected $renderTemplateStringDecider;

    /**
     *  @var callable
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
     * @param array $data
     * @return void
     */
    private function setEngines(array $engines): void
    {
        foreach ($engines as $ident => $engine) {
            $this->engines[$ident] = $engine;
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
     * @return bool
     */
    public function has($ident): bool
    {
        return isset($this->engines[$ident]);
    }

    /**
     * @param string $ident The view identifier.
     * @throws InvalidArgumentException If identifer is not recognized.
     * @return GenericView
     */
    public function get(string $ident)
    {
        if (!$this->has($ident)) {
            throw new InvalidArgumentException(sprintf(
                "Engine identifer must be one of \"%s\", \"%s\" given.",
                implode(', ', array_keys($this->engines)),
                $ident
            ));
        }

        return new  GenericView([
            'engine' => $this->engines[$ident],
        ]);
    }

    /**
     * @param string $ident The view identifier.
     * @throws InvalidArgumentException If identifer is not recognized.
     * @return self
     */
    public function using($ident)
    {
        if (!$this->has($ident)) {
            throw new InvalidArgumentException(sprintf(
                "Engine identifer must be one of \"%s\", \"%s\" given.",
                implode(', ', array_keys($this->engines)),
                $ident
            ));
        }

        $this->setEngine($this->engines[$ident]);
        return $this;
    }
}
