<?php

namespace Charcoal\Model;

use \Charcoal\Model\ModelInterface as ModelInterface;

use \Charcoal\Source\StorableInterface as StorableInterface;
use \Charcoal\Source\StorableTrait as StorableTrait;

use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatableTrait as validatableTrait;

use \Charcoal\View\ViewableInterface as ViewableInterface;
use \Charcoal\View\ViewableTrait as ViewableTrait;

/**
* An abstract class that implements most of ModelInterface.
*
* In addition to `ModelInterface`, the abstract model implements the `StorableInterface,
* `ValidatableInterface` and the `ViewableInterface`. Those interfaces are implemented
* (in parts, at least) with the `StorableTrait`, `ValidatableTrait` and the `ViewableTrait`.
*/
abstract class AbstractModel implements
    ModelInterface,
    StorableInterface,
    ValidatableInterface,
    ViewableInterface
{
    use StorableTrait;
    use ValidatableTrait;
    use ViewableTrait;

    public function pre_save()
    {
        return true;
    }

    public function post_save()
    {
        return true;
    }

    public function pre_update($properties=null)
    {
        return true;
    }

    public function post_update($properties=null)
    {
        return true;
    }

    public function pre_delete()
    {
        return true;
    }

    public function post_delete()
    {
        return true;
    }
}
