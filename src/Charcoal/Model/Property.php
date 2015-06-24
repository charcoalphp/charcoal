<?php

namespace Charcoal\Model;

use \PDO as PDO;

use \Charcoal\Property\AbstractProperty as AbstractProperty;

use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

class Property extends AbstractProperty
{
    /**
    * @return string
    */
    public function type()
    {
        return 'property';
    }

    /**
    * @param ValidatorInterface $v
    * @return boolean
    */
    public function validate(ValidatorInterface &$v = null)
    {
        if ($v === null) {
            $v = $this->validator();
        }

        $ret = true;
        $ret = parent::validate($v) && $ret;
        $ret = $this->validate_required() && $ret;
        $ret = $this->validate_unique() && $ret;

        return $ret;
    }

    /**
    * @return boolean
    */
    public function validate_required()
    {
        return true;
    }

    /**
    * @return boolean
    */
    public function validate_unique()
    {
        return true;
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
