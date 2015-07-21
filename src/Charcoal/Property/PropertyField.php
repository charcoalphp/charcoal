<?php

namespace Charcoal\Property;

// Intra-module (`charcoal-core`) dependencies
use \InvalidArgumentException as InvalidArgumentException;
use \PDO as PDO;

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
    private $_ident;
    /**
    * @var TranslationString $_label
    */
    private $_label;
    /**
    * @var string
    */
    private $_sql_type;
    /**
    * @var integer
    */
    private $_sql_pdo_type;
    /**
    * @var string
    */
    private $_extra;
    /**
    * @var mixed $_val
    */
    private $_val;
    /**
    * @var mixed $_default_val
    */
    private $_default_val;
    /**
    * @var boolean $_allow_null
    */
    private $_allow_null;

    /**
    * @param array $data
    * @return PropertyField Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['ident']) && $data['ident'] !== null) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['label']) && $data['label'] !== null) {
            $this->set_label($data['label']);
        }
        if (isset($data['sql_type']) && $data['sql_type'] !== null) {
            $this->set_sql_type($data['sql_type']);
        }
        if (isset($data['sql_pdo_type']) && $data['sql_pdo_type'] !== null) {
            $this->set_sql_pdo_type($data['sql_pdo_type']);
        }
        if (isset($data['extra']) && $data['extra'] !== null) {
            $this->set_extra($data['extra']);
        }
        if (isset($data['val'])) {
            $this->set_val($data['val']);
        }
        if (isset($data['default_val'])) {
            $this->set_default($data['default_val']);
        }
        if (isset($data['allow_null']) && $data['allow_null']) {
            $this->set_allow_null($data['allow_null']);
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
        $this->_ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->_ident;
    }

    /**
    * @param mixed $label
    * @return PropertyField Chainable
    */
    public function set_label($label)
    {
        $this->_label = new TranslationString($label);
        return $this;
    }

    /**
    * @return mixed
    */
    public function label()
    {
        return $this->_label;
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
        $this->_sql_type = $sql_type;
        return $this;
    }

    /**
    * @return string
    */
    public function sql_type()
    {
        return $this->_sql_type;
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
        $this->_sql_pdo_type = $sql_pdo_type;
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
        return $this->_sql_pdo_type;
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
        $this->_extra = $extra;
        return $this;
    }

    /**
    * @return string
    */
    public function extra()
    {
        if (!$this->_extra === null) {
            return '';
        }
        return $this->_extra;
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
        $this->_val = $val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function val()
    {
        return $this->_val;
    }

    /**
    * @param mixed $default_val
    * @return PropertyField Chainable
    */
    public function set_default_val($default_val)
    {
        $this->_default_val = $default_val;
        return $this;
    }

    /**
    * @return mixed
    */
    public function default_val()
    {
        return $this->_default_val;
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
        $this->_allow_null = $allow_null;
        return $this;
    }

    /**
    * @return boolean
    */
    public function allow_null()
    {
        return $this->_allow_null;
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
