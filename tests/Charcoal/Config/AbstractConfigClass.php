<?php

namespace Charcoal\Tests\Config;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

/**
* Concrete implementation of AbstractConfig for Unit Tests.
*/
class AbstractConfigClass extends AbstractConfig
{

    private $_foo;

    public function set_data($data)
    {
        if (isset($data['foo']) && $data['foo'] !== null) {
            $this->set_foo($data['foo']);
        }
        return $this;
    }

    public function set_foo($foo)
    {
        $this->_foo = $foo;
        return $this;
    }

    public function foo()
    {
        return $this->_foo;
    }
}
