<?php

namespace Charcoal\Model;

use \PDO as PDO;

use \Charcoal\Property\AbstractProperty as AbstractProperty;

use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

class Property extends AbstractProperty
{

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

    public function validate_required()
    {
        return true;
    }

    public function validate_unique()
    {
        return true;
    }

    public function sql_extra()
    {
        return '';
    }
    
    public function sql_type()
    {
        if ($this->multiple()) {
            return 'TEXT';
        } else {
            return 'VARCHAR(255)';
        }
    }

    public function sql_pdo_type()
    {
        return PDO::PARAM_STR;
    }

    public function save()
    {
        return $this->val();
    }
}
