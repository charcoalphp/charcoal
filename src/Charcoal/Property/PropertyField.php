<?php

namespace Charcoal\Property;

use \InvalidArgumentException;
use \PDO;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Tranlsation\TranslationString;

/**
*
*/
class PropertyField
{
    /**
    * @var string $_ident
    */
    private $ident;
    /**
    * @var TranslationString $_label
    */
    private $label;
    /**
    * @var string
    */
    private $sql_type;
    /**
    * @var integer
    */
    private $sql_pdo_type;
    /**
    * @var string
    */
    private $extra;
    /**
    * @var mixed $_val
    */
    private $val;
    /**
    * @var mixed $_default_val
    */
    private $default_val;
    /**
    * @var boolean $_allow_null
    */
    private $allow_null;

    /**
    * @param array $data
    * @return PropertyField Chainable
    */
    public function set_data(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, 'set_'.$prop];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException
    * @return PropertyField Chainable
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident must be a string.');
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param mixed $label
    * @return PropertyField Chainable
    */
    public function set_label($label)
    {
        $this->label = new TranslationString($label);
        return $this;
    }

    /**
    * @return mixed
    */
    public function label()
    {
        return $this->label;
    }

    /**
    * @param string $sql_type
    * @throws InvalidArgumentException
    * @return PropertyField Chainable
    */
    public function set_sql_type($sql_type)
    {
        if (!is_string($sql_type)) {
            throw new InvalidArgumentException('Sql Type must be a string.');
        }
        $this->sql_type = $sql_type;
        return $this;
    }

    /**
    * @return string
    */
    public function sql_type()
    {
        return $this->sql_type;
    }

    /**
    * @param integer $sql_pdo_type
    * @throws InvalidArgumentException
    * @return PropertyField Chainable
    */
    public function set_sql_pdo_type($sql_pdo_type)
    {
        if (!is_integer($sql_pdo_type)) {
            throw new InvalidArgumentException('PDO Type must be an integer.');
        }
        $this->sql_pdo_type = $sql_pdo_type;
        return $this;
    }

    /**
    * @return integer
    */
    public function sql_pdo_type()
    {
        if ($this->val() === null) {
            return PDO::PARAM_NULL;
        }
        return $this->sql_pdo_type;
    }

    /**
    * @param mixed $extra
    * @throws InvalidArgumentException
    * @return PropertyField Chainable
    */
    public function set_extra($extra)
    {
        if (!is_string($extra)) {
            throw new InvalidArgumentException('Extra must be a string.');
        }
        $this->extra = $extra;
        return $this;
    }

    /**
    * @return string
    */
    public function extra()
    {
        if (!$this->extra === null) {
            return '';
        }
        return $this->extra;
    }

    /**
    * @param mixed $val
    * @return PropertyField Chainable
    */
    public function set_val($val)
    {
        /*
        if (!is_scalar($val)) {
            throw new \InvalidArgumentException('Val must be scalar.');
        }
        */
        $this->val = $val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function val()
    {
        return $this->val;
    }

    /**
    * @param mixed $default_val
    * @return PropertyField Chainable
    */
    public function set_default_val($default_val)
    {
        $this->default_val = $default_val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function default_val()
    {
        return $this->default_val;
    }

    /**
    * @param boolean $allow_null
    * @throws InvalidArgumentException
    * @return PropertyField Chainable
    */
    public function set_allow_null($allow_null)
    {
        if (!is_bool($allow_null)) {
            throw new InvalidArgumentException('Allow null must be a boolean.');
        }
        $this->allow_null = $allow_null;
        return $this;
    }

    /**
    * @return boolean
    */
    public function allow_null()
    {
        return $this->allow_null;
    }

    /**
    * @return string
    */
    public function sql()
    {
        $ident = $this->ident();
        if (!$ident) {
            return '';
        }

        $sql_type = $this->sql_type();
        $null = (($this->allow_null() === false) ? ' NOT NULL ' : '');
        $extra = $this->extra() ? ' '.$this->extra().' ' : '';
        $default = ($this->default_val() ? ' DEFAULT \''.addslashes($this->default_val()).'\' ' : '');
        $comment = ($this->label() ? ' COMMENT \''.addslashes($this->label()).'\' ' : '');

        return '`'.$ident.'` '.$sql_type.$null.$extra.$default.$comment;
    }
}
