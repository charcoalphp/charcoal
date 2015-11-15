<?php

namespace Charcoal\Core;

class ClassNameFactory extends AbstractFactory
{
    /**
    * {@inheritdoc}
    */
    public function classname($type)
    {
        return $type;
    }

    /**
    * {@inheritdoc}
    */
    public function validate($type)
    {
        return class_exists($type);
    }
}
