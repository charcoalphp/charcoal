<?php

namespace Charcoal\View;

// From PSR-7
use Psr\Http\Message\ResponseInterface;

// From 'charcoal-view'
use Charcoal\View\ViewInterface;

/**
 * Provides a PSR-7 renderer that uses a Charcoal View.
 *
 * A "PSR-7" renderer is a service that renders a template identifier inside a HTTP Response
 *
 * ## Dependencies
 * - `view` A "Charcoal View", which is any class that implements `\Charcoal\View\ViewInterface`.
 */
class Renderer
{
    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * @param array $data The constructor dependencies.
     */
    public function __construct(array $data)
    {
        $this->setView($data['view']);
    }

    /**
     * @param ResponseInterface $response      The HTTP response.
     * @param string            $templateIdent The template identifier to load and render.
     * @param mixed             $context       The view controller / context.
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $templateIdent, $context = null)
    {
        $rendered = $this->view->render($templateIdent, $context);
        $response->getBody()->write($rendered);
        return $response;
    }

    /**
     * @param ViewInterface $view The view instance to use.
     * @return Renderer Chainable
     */
    private function setView(ViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }
}
