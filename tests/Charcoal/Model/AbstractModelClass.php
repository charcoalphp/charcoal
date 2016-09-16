<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Model\AbstractModel as AbstractModel;

/**
 * Concrete implementation of AbstractModel for Unit Tests.
 */
class AbstractModelClass extends AbstractModel
{
    public $id;

    public $foo;

    public function key()
    {
        return 'id';
    }
}
