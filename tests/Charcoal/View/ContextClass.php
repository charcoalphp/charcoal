<?php

namespace Charcoal\Tests\View;

class ContextClass
{
    private $_foo;

    public $baz;

    public function foo()
    {
        return $this->_foo;
    }

    public function set_foo($val)
    {
        $this->_foo = $val;
        return $this;
    }
}
