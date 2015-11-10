<?php

namespace Charcoal\View;

// PSR-7 (http)
use Psr\Http\Message\ResponseInterface;

// Local namespace dependencies
use \Charcoal\View\ViewInterface;

/**
* Provides a PSR-7 renderer that uses a Charcoal View.
*
* A "Charcoal View" is any class that implements `\Charcoal\View\ViewInterface`.
*/
class Renderer
{
    /**
    * @var ViewInterface $view
    */
    private $view;

    /**
    * @param array $data The constructor dependencies
    */
    public function __construct(array $data)
    {
        $this->set_view($data['view']);
    }

    /**
    * @param ViewInterface $view
    * @return Renderer Chainable
    */
    public function set_view(ViewInterface $view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
    * @return ViewInterface
    */
    public function view()
    {
        return $this->view;
    }

    /**
    * @param ResponseInterface $response
    * @param string $template
    * @param mixed $context
    * @return ResponseInterface
    */
    public function render(ResponseInterface $response, $template, $context = null)
    {
         $response->getBody()->write($this->view()->render_template($template, $context));
         return $response;
    }
}
