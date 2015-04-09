<?php

namespace Charcoal\View;

interface ViewInterface
{
    public function set_data($data);
    public function set_template($template);
    public function template();
    public function set_context($context);
    public function context();
    public function set_controller(ViewControllerInterface $controller);
    public function controller();
    public function render($template=null, $context=null);
    public function render_template($template_ident='', $context=null);
}
