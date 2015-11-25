<?php

namespace Charcoal\Property;

// Dependencies from `PHP`
use \PDO;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Property\AbstractProperty;

/**
* The most basic (generic) property possible, from abstract.
*/
class GenericProperty extends AbstractProperty
{
    /**
    * @return string
    */
    public function type()
    {
        return 'property';
    }

    /**
    * @return string
    */
    public function sql_extra()
    {
        return '';
    }

    /**
    * @return string
    */
    public function sql_type()
    {
        if ($this->multiple()) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
    }

    /**
    * @return integer
    */
    public function sql_pdo_type()
    {
        return PDO::PARAM_STR;
    }

    /**
    * @return mixed
    */
    public function save()
    {
        return $this->val();
    }
}
