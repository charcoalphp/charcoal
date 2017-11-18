<?php

namespace Charcoal\Tests\Config;

use \Charcoal\Config\AbstractEntity;

class AbstractEntityClass extends AbstractEntity
{
    private $foo;

    public function setFoo($foo)
    {
        $this->foo = ((int)$foo+10);
        return $this;
    }

    public function foo()
    {
        return 'foo is '.$this->foo;
    }
}
