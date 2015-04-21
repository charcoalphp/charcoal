<?php

namespace Charcoal\Tests\Core;

use \Charcoal\Core\IndexableInterface as IndexableInterface;
use \Charcoal\Core\IndexableTrait as IndexableTrait;

/**
* Concrete implementation of CoreableTrait for Unit Tests.
*/
class IndexableClass implements IndexableInterface
{
    use IndexableTrait;

    private $_foo;

    public function set_foo($foo)
    {
        $this->_foo = $foo;
    }

    public function foo()
    {
        return $this->_foo;
    }
}
