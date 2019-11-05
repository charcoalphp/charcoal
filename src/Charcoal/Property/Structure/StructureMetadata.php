<?php

namespace Charcoal\Property\Structure;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\AbstractMetadata;

/**
 *
 */
class StructureMetadata extends AbstractMetadata
{
    /**
     * The metadata identifier.
     *
     * @var string|null
     */
    private $ident;

    /**
     * Store the admin module config.
     *
     * @var array
     */
    private $admin = [];

    /**
     * Set the metadata identifier.
     *
     * @param  string $ident The metadata identifier.
     * @throws InvalidArgumentException If identifier is not a string.
     * @return StructureMetadata Chainable
     */
    public function setIdent($ident)
    {
        if ($ident === null) {
            $this->ident = null;
            return $this;
        }

        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    '[%s] Identifier must be a string; received %s',
                    get_called_class(),
                    (is_object($ident) ? get_class($ident) : gettype($ident))
                )
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the metadata identifier.
     *
     * @return string|null
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set the object's default values.
     *
     * @param  array $data An associative array.
     * @return StructureMetadata
     */
    public function setDefaultData(array $data)
    {
        foreach ($data as $key => $val) {
            $key = $this->camelize($key);
            $this->defaultData[$key] = $val;
        }

        return $this;
    }

    /**
     * Set the properties.
     *
     * @param  array $properties One or more properties.
     * @return StructureMetadata
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $propertyIdent => $propertyMetadata) {
            $propertyIdent = $this->camelize($propertyIdent);
            if (isset($this->properties[$propertyIdent])) {
                $this->properties[$propertyIdent] = array_replace_recursive(
                    $this->properties[$propertyIdent],
                    $propertyMetadata
                );
            } else {
                $this->properties[$propertyIdent] = $propertyMetadata;
            }
        }

        return $this;
    }

    /**
     * Determine if the structure has the given property.
     *
     * @param  string $propertyIdent The property identifier to lookup.
     * @throws InvalidArgumentException If the identifier argument is not a string.
     * @return boolean
     */
    public function hasProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property Ident must be a string.'
            );
        }

        $propertyIdent = $this->camelize($propertyIdent);
        return isset($this->properties[$propertyIdent]);
    }

    /**
     * Retrieve the admin module's metadata.
     *
     * @return array
     */
    public function admin()
    {
        return $this->admin;
    }

    /**
     * Set the admin module's metadata.
     *
     * @param  array $data Metadata.
     * @return StructureMetadata
     */
    public function setAdmin(array $data)
    {
        $this->admin = array_replace_recursive($this->admin, $data);

        return $this;
    }
}
