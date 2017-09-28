<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;
use Charcoal\Property\StorablePropertyInterface;

/**
 * Contains the field property.
 */
trait FieldTrait
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
    protected $tableName;

    /**
     * Set model property key or source field key.
     *
     * @param  string|PropertyInterface $property The related property.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return Order Chainable
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
     * Set the table name identifying a field.
     *
     * @param  string $tableRef The table name or alias.
     * @throws InvalidArgumentException If the parameter is not a string.
     * @return self
     */
    public function setTableName($tableRef)
    {
        if ($tableRef === null) {
            $this->tableName = $tableRef;
            return $this;
        }

        if (!is_string($tableRef)) {
            throw new InvalidArgumentException(
                'Table Name must be a string.'
            );
        }

        if ($tableRef === '') {
            throw new InvalidArgumentException(
                'Table Name can not be empty.'
            );
        }

        $this->tableName = $tableRef;

        return $this;
    }

    /**
     * Determine if a table name is assigned.
     *
     * @return boolean
     */
    public function hasTableName()
    {
        return !empty($this->tableName);
    }

    /**
     * Retrieve the table name identifying a field.
     *
     * @return string|null The related table.
     */
    public function tableName()
    {
        return $this->tableName;
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
            } else {
                return [ $property ];
            }
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
        $property = $this->property();
        if ($property instanceof PropertyInterface) {
            return $property->ident();
        } else {
            return $property;
        }
    }

    /**
     * Retrieve the property's fully-qualified field names.
     *
     * @return string[]
     */
    public function fieldIdentifiers()
    {
        $identifiers = [];
        $fieldNames  = $this->fieldNames();
        foreach ($fieldNames as $fieldName) {
            $identifiers[] = $this->quoteIdentifier($fieldName);
        }

        return $identifiers;
    }

    /**
     * Retrieve the fully-qualified field name.
     *
     * @return string|null
     */
    public function fieldIdentifier()
    {
        return $this->quoteIdentifier($this->fieldName());
    }

    /**
     * Quote the given field name.
     *
     * @param  string $identifier The field name.
     * @throws InvalidArgumentException If the parameter is not a string.
     * @return string
     */
    protected function quoteIdentifier($identifier)
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

        $tableName = $this->tableName();
        if ($tableName) {
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
}
