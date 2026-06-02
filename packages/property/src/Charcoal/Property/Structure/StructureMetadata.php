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
     */
    private ?string $ident = null;

    /**
     * Store the admin module config.
     */
    private array $admin = [];

    /**
     * Set the metadata identifier.
     *
     * @param  string $ident The metadata identifier.
     * @throws InvalidArgumentException If identifier is not a string.
     * @return StructureMetadata Chainable
     */
    public function setIdent($ident): static
    {
        if ($ident === null) {
            $this->ident = null;
            return $this;
        }

        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                sprintf(
                    '[%s] Identifier must be a string; received %s',
                    static::class,
                    (get_debug_type($ident))
                )
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Retrieve the metadata identifier.
     */
    public function ident(): ?string
    {
        return $this->ident;
    }

    /**
     * Set the object's default values.
     *
     * @param  array $data An associative array.
     */
    #[\Override]
    public function setDefaultData(array $data): static
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
     */
    #[\Override]
    public function setProperties(array $properties): static
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
     */
    public function hasProperty($propertyIdent): bool
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
     */
    public function admin(): array
    {
        return $this->admin;
    }

    /**
     * Set the admin module's metadata.
     *
     * @param  array $data Metadata.
     */
    public function setAdmin(array $data): static
    {
        $this->admin = array_replace_recursive($this->admin, $data);

        return $this;
    }
}
