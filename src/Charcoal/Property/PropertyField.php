<?php

namespace Charcoal\Property;

use PDO;
use InvalidArgumentException;

/**
 *
 */
class PropertyField
{
    /**
     * @var string
     */
    private $ident;

    /**
     * @var string
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
     * @var mixed
     */
    private $val;

    /**
     * @var mixed
     */
    private $defaultVal;

    /**
     * @var boolean
     */
    private $allowNull;

    /**
     * Holds a list of all snake_case strings.
     *
     * @var string[]
     */
    protected static $snakeCache = [];

    /**
     * @param  array $data The field data.
     * @return PropertyField Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['ident'])) {
            $this->setIdent($data['ident']);
        }
        if (isset($data['label'])) {
            $this->setLabel($data['label']);
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
     * @param  string $ident The field identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return PropertyField Chainable
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Identifier must be a string'
            );
        }
        $this->ident = $this->snakeize($ident);
        return $this;
    }

    /**
     * @return string|null
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param  string $label The field label.
     * @throws InvalidArgumentException If the label is not a string.
     * @return PropertyField Chainable
     */
    public function setLabel($label)
    {
        if (!is_string($label) && $label !== null) {
            throw new InvalidArgumentException(
                'Label must be a string'
            );
        }
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function label()
    {
        return $this->label;
    }

    /**
     * @param  string $sqlType The field SQL column type.
     * @throws InvalidArgumentException If the SQL type is not a string.
     * @return PropertyField Chainable
     */
    public function setSqlType($sqlType)
    {
        if (!is_string($sqlType) && $sqlType !== null) {
            throw new InvalidArgumentException(
                'SQL Type must be a string'
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
     * @param  integer $sqlPdoType The field PDO type.
     * @throws InvalidArgumentException If the PDO type is not an integer.
     * @return PropertyField Chainable
     */
    public function setSqlPdoType($sqlPdoType)
    {
        if (!is_integer($sqlPdoType)) {
            throw new InvalidArgumentException(
                'PDO Type must be an integer'
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
     * @param  string|null $extra The extra.
     * @throws InvalidArgumentException If the extra is not a string.
     * @return PropertyField Chainable
     */
    public function setExtra($extra)
    {
        if (!is_string($extra) && $extra !== null) {
            throw new InvalidArgumentException(
                'Extra must be a string'
            );
        }
        $this->extra = $extra;
        return $this;
    }

    /**
     * @return string|null
     */
    public function extra()
    {
        return $this->extra;
    }

    /**
     * @param  string $encoding The encoding and collation.
     * @throws InvalidArgumentException If the encoding is not a string.
     * @return PropertyField Chainable
     */
    public function setSqlEncoding($encoding)
    {
        if (!is_string($encoding) && $encoding !== null) {
            throw new InvalidArgumentException(
                'Encoding must be a string'
            );
        }
        $this->sqlEncoding = $encoding;
        return $this;
    }

    /**
     * @return string|null
     */
    public function sqlEncoding()
    {
        return $this->sqlEncoding;
    }

    /**
     * @param  mixed $val The field value.
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
     * @param  mixed $defaultVal The default field value.
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
     * @param  boolean $allowNull The field allow null flag.
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
     * Generates the SQL table column.
     *
     * @return string|null
     */
    public function sql()
    {
        $ident = $this->ident();
        if (!$ident) {
            return null;
        }

        $dataType = $this->sqlType();
        if (!$dataType) {
            return null;
        }

        $parts = [
            sprintf('`%s`', $ident),
            $dataType
        ];

        if ($this->allowNull() === false) {
            $parts[] = 'NOT NULL';
        }

        $extra = $this->extra();
        if ($extra) {
            $parts[] = $extra;
        }

        $encoding = $this->sqlEncoding();
        if ($encoding) {
            $parts[] = $encoding;
        }

        $default = $this->defaultVal();
        if ($default) {
            $parts[] = sprintf('DEFAULT \'%s\'', addslashes($default));
        }

        $comment = $this->label();
        if ($comment) {
            $parts[] = sprintf('COMMENT \'%s\'', addslashes($comment));
        }

        return implode(' ', $parts);
    }

    /**
     * Transform a string from "camelCase" to "snake_case".
     *
     * @param  string $value The string to snakeize.
     * @return string The snake_case string.
     */
    protected function snakeize($value)
    {
        $key = $value;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        $value = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));

        static::$snakeCache[$key] = $value;

        return static::$snakeCache[$key];
    }
}
