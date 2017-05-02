<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;

/**
 *
 */
class PropertyField
{
    use TranslatorAwareTrait;

    /**
     * @var string $ident
     */
    private $ident;

    /**
     * @var \Charcoal\Translator\Translation $Label
     */
    private $label;

    /**
     * @var string
     */
    private $sqlType;

    /**
     * @var integer
     */
    private $sqlPdoType;

    /**
     * @var string
     */
    private $sqlEncoding;

    /**
     * @var string
     */
    private $extra;

    /**
     * @var mixed $Val
     */
    private $val;

    /**
     * @var mixed $_defaultVal
     */
    private $defaultVal;

    /**
     * @var boolean $_allowNull
     */
    private $allowNull;

    /**
     * @param array $data Constructor options.
     */
    public function __construct(array $data)
    {
        $this->setTranslator($data['translator']);
    }

    /**
     * @param array $data The field data.
     * @return PropertyField Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['ident'])) {
            $this->setIdent($data['ident']);
        }
        if (isset($data['sqlType'])) {
            $this->setSqlType($data['sqlType']);
        }
        if (isset($data['sqlPdoType'])) {
            $this->setSqlPdoType($data['sqlPdoType']);
        }
        if (isset($data['sqlEncoding'])) {
            $this->setSqlEncoding($data['sqlEncoding']);
        }
        if (isset($data['extra'])) {
            $this->setExtra($data['extra']);
        }
        if (isset($data['val'])) {
            $this->setVal($data['val']);
        }
        if (isset($data['defaultVal'])) {
            $this->setDefaultVal($data['defaultVal']);
        }
        if (isset($data['allowNull'])) {
            $this->setAllowNull($data['allowNull']);
        }

        return $this;
    }

    /**
     * @param string $ident The field identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return PropertyField Chainable
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Identifier must be a string.'
            );
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
     * @param mixed $label The field label.
     * @return PropertyField Chainable
     */
    public function setLabel($label)
    {
        $this->label = $this->translator()->translation($label);
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
     * @param string $sqlType The field sql column type.
     * @throws InvalidArgumentException If the sql type is not a string.
     * @return PropertyField Chainable
     */
    public function setSqlType($sqlType)
    {
        if (!is_string($sqlType)) {
            throw new InvalidArgumentException(
                'SQL Type must be a string.'
            );
        }
        $this->sqlType = $sqlType;
        return $this;
    }

    /**
     * @return string
     */
    public function sqlType()
    {
        return $this->sqlType;
    }

    /**
     * @param integer $sqlPdoType The field PDO type.
     * @throws InvalidArgumentException If the PDO type is not an integer.
     * @return PropertyField Chainable
     */
    public function setSqlPdoType($sqlPdoType)
    {
        if (!is_integer($sqlPdoType)) {
            throw new InvalidArgumentException(
                'PDO Type must be an integer.'
            );
        }
        $this->sqlPdoType = $sqlPdoType;
        return $this;
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        if ($this->val() === null) {
            return PDO::PARAM_NULL;
        }
        return $this->sqlPdoType;
    }

    /**
     * @param string $extra The extra.
     * @throws InvalidArgumentException If the extra is not a string.
     * @return PropertyField Chainable
     */
    public function setExtra($extra)
    {
        if (!is_string($extra)) {
            throw new InvalidArgumentException(
                'Extra must be a string.'
            );
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
     * @param string $encoding The encoding and collation.
     * @throws InvalidArgumentException If the encoding is not a string.
     * @return PropertyField Chainable
     */
    public function setSqlEncoding($encoding)
    {
        if (!is_string($encoding)) {
            throw new InvalidArgumentException(
                'Encoding must be a string.'
            );
        }
        $this->sqlEncoding = $encoding;
        return $this;
    }

    /**
     * @return string
     */
    public function sqlEncoding()
    {
        if (!$this->sqlEncoding === null) {
            return '';
        }
        return $this->sqlEncoding;
    }

    /**
     * @param mixed $val The field value.
     * @return PropertyField Chainable
     */
    public function setVal($val)
    {
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
     * @param mixed $defaultVal The default field value.
     * @return PropertyField Chainable
     */
    public function setDefaultVal($defaultVal)
    {
        $this->defaultVal = $defaultVal;
        return $this;
    }

    /**
     * @return mixed
     */
    public function defaultVal()
    {
        return $this->defaultVal;
    }

    /**
     * @param boolean $allowNull The field allow null flag.
     * @return PropertyField Chainable
     */
    public function setAllowNull($allowNull)
    {
        $this->allowNull = !!$allowNull;
        return $this;
    }

    /**
     * @return boolean
     */
    public function allowNull()
    {
        return $this->allowNull;
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

        $sqlType     = $this->sqlType();
        $null        = (($this->allowNull() === false) ? ' NOT NULL ' : '');
        $extra       = $this->extra() ? ' '.$this->extra().' ' : '';
        $sqlEncoding = $this->sqlEncoding() ? ' '.$this->sqlEncoding().' ' : '';
        $default     = ($this->defaultVal() ? ' DEFAULT \''.addslashes($this->defaultVal()).'\' ' : '');
        $comment     = ($this->label() ? ' COMMENT \''.addslashes($this->label()).'\' ' : '');

        return '`'.$ident.'` '.$sqlType.$null.$extra.$sqlEncoding.$default.$comment;
    }
}
