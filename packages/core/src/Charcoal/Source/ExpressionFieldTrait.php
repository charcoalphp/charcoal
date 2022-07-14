<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\StorablePropertyInterface;
// From 'charcoal-core'
use Charcoal\Source\Expression;

/**
 * Provides the field and table properties.
 *
 * Satisfies {@see ExpressionFieldInterface}.
 */
trait ExpressionFieldTrait
{
    /**
     * The model property name or field name or expression to be used in the left hand side of the operator.
     *
     * @var string|PropertyInterface|null
     */
    protected $property;

    /**
     * The table related to the field identifier.
     *
     * @var string|null
     */
    protected $table;

    /**
     * Holds a list of all snake_case strings.
     *
     * @var string[]
     */
    protected static $snakeCache = [];

    /**
     * Set model property key or source field key.
     *
     * @param  string|PropertyInterface $property The related property.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return self
     */
    public function setProperty($property)
    {
        if ($property === null) {
            $this->property = $property;
            return $this;
        }

        if ($property instanceof PropertyInterface) {
            if (empty($property->ident())) {
                throw new InvalidArgumentException(
                    'Property must have an identifier.'
                );
            }

            $this->property = $property;
            return $this;
        }

        if (!is_string($property)) {
            throw new InvalidArgumentException(
                'Property must be a string.'
            );
        }

        if ($property === '') {
            throw new InvalidArgumentException(
                'Property can not be empty.'
            );
        }

        $this->property = $property;
        return $this;
    }

    /**
     * Determine if a model property or source field key is assigned.
     *
     * @return boolean
     */
    public function hasProperty()
    {
        return !empty($this->property);
    }

    /**
     * Retrieve the model property or source field key.
     *
     * @return string|PropertyInterface|null The related property.
     */
    public function property()
    {
        return $this->property;
    }

    /**
     * Set the reference to the table related to the field.
     *
     * @param  string $table The table name or alias.
     * @throws InvalidArgumentException If the parameter is not a string.
     * @return self
     */
    public function setTable($table)
    {
        if ($table === null) {
            $this->table = $table;
            return $this;
        }

        if (!is_string($table)) {
            throw new InvalidArgumentException(
                'Table reference must be a string.'
            );
        }

        if ($table === '') {
            throw new InvalidArgumentException(
                'Table reference can not be empty.'
            );
        }

        $this->table = $table;
        return $this;
    }

    /**
     * Determine if a table is assigned.
     *
     * @return boolean
     */
    public function hasTable()
    {
        return !empty($this->table);
    }

    /**
     * Retrieve the table related to the field.
     *
     * @return string|null The related table.
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * Determine if the model property has any fields.
     *
     * @return boolean
     */
    public function hasFields()
    {
        if ($this->hasProperty()) {
            $property = $this->property();
            if ($property instanceof StorablePropertyInterface) {
                return (count($property->fieldNames()) > 0);
            }

            return true;
        }

        return false;
    }

    /**
     * Retrieve the model property's field names.
     *
     * @todo Load Property from associated model metadata.
     * @return array
     */
    public function fieldNames()
    {
        if ($this->hasProperty()) {
            $property = $this->property();
            if ($property instanceof StorablePropertyInterface) {
                return $property->fieldNames();
            }

            $property = $this->snakeize($property);
            return [ $property ];
        }

        return [];
    }

    /**
     * Retrieve the property's field name.
     *
     * @return string|null
     */
    public function fieldName()
    {
        if ($this->hasProperty()) {
            $property = $this->property();
            if ($property instanceof PropertyInterface) {
                return $property->fieldIdent();
            }

            return $this->snakeize($property);
        }

        return null;
    }

    /**
     * Retrieve the property's fully-qualified field names.
     *
     * @return string[]
     */
    public function fieldIdentifiers()
    {
        $identifiers = [];
        $tableName   = $this->table();
        $fieldNames  = $this->fieldNames();
        foreach ($fieldNames as $fieldName) {
            $identifiers[] = Expression::quoteIdentifier($fieldName, $tableName);
        }

        return $identifiers;
    }

    /**
     * Retrieve the fully-qualified field name.
     *
     * @return string
     */
    public function fieldIdentifier()
    {
        $tableName = $this->table();
        $fieldName = $this->fieldName();

        return Expression::quoteIdentifier($fieldName, $tableName);
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
