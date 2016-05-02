<?php

namespace Charcoal\Tests\Source;

use \Charcoal\Config\AbstractEntity;

use \Charcoal\Source\StorableInterface;
use \Charcoal\Source\StorableTrait;

class StorableClass extends AbstractEntity implements StorableInterface
{
    use StorableTrait;

    public function createSource()
    {
        return [];
    }

    protected function preSave()
    {
        return true;
    }

    protected function postSave()
    {
        return true;
    }

    protected function preUpdate()
    {
        return true;
    }

    protected function postUpdate()
    {
        return true;
    }

    protected function preDelete()
    {
        return true;
    }

    protected function postDelete()
    {
        return true;
    }
}
