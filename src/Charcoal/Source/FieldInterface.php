<?php

namespace Charcoal\Source;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

/**
 * Defines a field property.
 */
interface FieldInterface
{
    /**
     * Set model property key or source field key.
     *
     * @param  string|PropertyInterface $property The related property.
     * @return self
     */
    public function setProperty($property);

    /**
     * Determine if a model property or source field key is assigned.
     *
     * @return boolean
     */
    public function hasProperty();

    /**
     * Retrieve the model property or source field key.
     *
     * @return string|PropertyInterface|null The related property.
     */
    public function property();

    /**
     * Set the table name identifying a field.
     *
     * @param  string $reference The table name or alias.
     * @return self
     */
    public function setTableName($reference);

    /**
     * Determine if a table is assigned.
     *
     * @return boolean
     */
    public function hasTableName();

    /**
     * Retrieve the table name identifying a field.
     *
     * @return string|null The related table.
     */
    public function tableName();
}
