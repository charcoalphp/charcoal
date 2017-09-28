<?php

namespace Charcoal\Source;

use DateTimeInterface;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\DateTimeProperty;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;

/**
 * Represents a query expression.
 */
abstract class AbstractExpression implements
    ExpressionInterface
{
    /**
     * Raw query expression.
     *
     * @var string|null
     */
    protected $string;

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
     * @param  array $data The expression data.
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

        if (isset($data['string'])) {
            $this->setString($data['string']);
        }

        return $this;
    }

    /**
     * Retrieve the default values.
     *
     * @return array<string,mixed>
     */
    public function defaultData()
    {
        return [
            'name'   => null,
            'active' => true,
            'string' => null,
        ];
    }

    /**
     * Retrieve the expression data structure.
     *
     * @return array<string,mixed>
     */
    public function data()
    {
        $data = [
            'name'   => $this->name(),
            'active' => $this->active(),
            'string' => $this->string(),
        ];

        return array_diff_assoc($data, $this->defaultData());
    }

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
     * Set the custom query expression.
     *
     * @param  string|null $expr The custom query expression.
     * @throws InvalidArgumentException If the parameter is not a valid string expression.
     * @return self
     */
    public function setString($expr)
    {
        if ($expr !== null) {
            if (!is_string($expr)) {
                throw new InvalidArgumentException(
                    'Custom expression must be a string.'
                );
            }

            $expr = trim($expr);
            if ($expr === '') {
                $expr = null;
            }
        }

        $this->string = $expr;

        return $this;
    }

    /**
     * Determine if the expression has a custom query.
     *
     * @return boolean
     */
    public function hasString()
    {
        return !(empty($this->string) && !is_numeric($this->string));
    }

    /**
     * Retrieve the custom query expression.
     *
     * @return string|null A custom query expression.
     */
    public function string()
    {
        return $this->string;
    }

    /**
     * Parse the given value.
     *
     * @param  mixed $value The value to be normalized.
     * @return mixed Returns the parsed value.
     */
    public static function parseValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d H:i:s');
        } elseif ($value instanceof DateTimeProperty) {
            $value = $value->storageVal($value->val());
        } elseif (is_string($value)) {
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            }
        }

        return $value;
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
     * @see    JsonSerializable
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data();
    }

    /**
     * @see    Serializable
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data());
    }

    /**
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
