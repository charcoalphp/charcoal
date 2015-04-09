<?php

namespace Charcoal\View;

interface ViewControllerInterface
{
    public function set_context($context);
    public function context();
}
