<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\AbstractModel as AbstractModel;

/**
* Concrete implementation of AbstractModel for Unit Tests.
*/
class AbstractModelClass extends AbstractModel
{
    public $foo;

    public $id;

    public function id()
    {
        return $this->id;
    }

    public function set_id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function key()
    {
        return 'id';
    }
}
