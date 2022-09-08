<?php

namespace Charcoal\Source;

use DateTimeInterface;
use InvalidArgumentException;
// From 'charcoal-property'
use Charcoal\Property\DateTimeProperty;
// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;

/**
 * Represents the basic structure of a query expression.
 */
abstract class AbstractExpression implements
    ExpressionInterface
{
    /**
     * Expression name.
     *
     * @var string|null
     */
    protected $name;

    /**
     * Whether the expression is active.
     *
     * @var boolean
     */
    protected $active = true;

    /**
     * Set the expression data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     * @return self
     */
    public function setData(array $data)
    {
        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['active'])) {
            $this->setActive($data['active']);
        }

        return $this;
    }

    /**
     * Retrieve the default values.
     *
     * @return array<string,mixed> An associative array.
     */
    abstract public function defaultData();

    /**
     * Retrieve the expression data structure.
     *
     * @return array<string,mixed> An associative array.
     */
    abstract public function data();

    /**
     * Set whether the expression is active or not.
     *
     * @param  boolean $active The active flag.
     * @return self
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * Determine if the expression is active or not.
     *
     * @return boolean TRUE if active, FALSE is disabled.
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Set the expression name.
     *
     * @param  string $name A unique identifier.
     * @throws InvalidArgumentException If the expression name is not a string.
     * @return self
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Expression name must be a string');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * Retrieve the expression name.
     *
     * @return string|null
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Parse the given value.
     *
     * @param  mixed $value The value to be parsed.
     * @return mixed Returns the parsed value.
     */
    public static function parseValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof DateTimeProperty) {
            $value = $value->storageVal($value->val());
        } elseif (is_string($value)) {
            $str = strtolower($value);
            if ($str === 'true') {
                $value = true;
            } elseif ($str === 'false') {
                $value = false;
            }
        }

        return $value;
    }

    /**
     * Quote the given scalar value.
     *
     * @param  mixed $value The value to be quoted.
     * @return mixed Returns:
     *     - If $value is not a scalar value, the value is returned intact.
     *     - if $value is a boolean, the value is cast to an integer.
     *     - If $value is not a number, the value is stringified
     *       and wrapped in double quotes.
     */
    public static function quoteValue($value)
    {
        $value = static::parseValue($value);

        if (!is_scalar($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return (int)$value;
        }

        if (!is_numeric($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES);
            $value = sprintf('"%s"', $value);
        }

        return $value;
    }

    /**
     * Quote the given field name.
     *
     * @param  string      $identifier The field name.
     * @param  string|null $tableName  If provided, the table name is prepended to the $identifier.
     * @throws InvalidArgumentException If the parameters are not string.
     * @return string
     */
    public static function quoteIdentifier($identifier, $tableName = null)
    {
        if ($identifier === null || $identifier === '') {
            return '';
        }

        if (!is_string($identifier)) {
            throw new InvalidArgumentException(sprintf(
                'Field Name must be a string, received %s',
                is_object($identifier) ? get_class($identifier) : gettype($identifier)
            ));
        }

        if ($tableName !== null) {
            if (!is_string($tableName)) {
                throw new InvalidArgumentException(sprintf(
                    'Table Name must be a string, received %s',
                    is_object($tableName) ? get_class($tableName) : gettype($tableName)
                ));
            }

            if ($tableName === '') {
                throw new InvalidArgumentException(
                    'Table Name can not be empty.'
                );
            }

            if ($identifier === '*') {
                $template = '%1$s.*';
            } else {
                $template = '%1$s.`%2$s`';
            }

            return sprintf($template, $tableName, $identifier);
        }

        if ($identifier === '*') {
            return $identifier;
        } else {
            return sprintf('`%1$s`', $identifier);
        }
    }

    /**
     * Compare two values.
     *
     * @param  mixed $a The custom value.
     * @param  mixed $b The default value.
     * @return integer
     */
    public static function diffValues($a, $b)
    {
        if ($a === $b) {
            return 0;
        }

        return 1;
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed $value The value to be checked.
     * @return boolean
     */
    public static function isCallable($value)
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Retrieve the expression data that can be serialized with {@see json_encode()}.
     *
     * Convert the expression to its JSON representation.
     * Convert the expression into something JSON serializable.
     * Returns an array of parameters to serialize when this is serialized with json_encode().
     *
     * @see    JsonSerializable
     * @uses   self::diffValues()
     * @return array<string,mixed> An associative array containing only mutated values.
     */
    public function jsonSerialize()
    {
        return array_udiff_assoc($this->data(), $this->defaultData(), [ $this, 'diffValues' ]);
    }

    /**
     * Generate a storable representation of the expression object.
     *
     * @see    Serializable
     * @return string Returns a string containing a byte-stream representation of the object.
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * Convert the serialized data into an expression object.
     *
     * @see    Serializable
     * @param  string $data The serialized data.
     * @return void
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->setData($data);
    }
}
