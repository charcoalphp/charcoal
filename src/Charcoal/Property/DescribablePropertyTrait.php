<?php

namespace Charcoal\Property;

use InvalidArgumentException;
use RuntimeException;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

/**
 * Provides an implementation of {@see DescribablePropertyInterface}, as a trait, for models.
 *
 * Requires {@see \Charcoal\Model\DescribableInterface}.
 */
trait DescribablePropertyTrait
{
    /**
     * Store the factory instance for the current class.
     *
     * @var FactoryInterface
     */
    protected $propertyFactory;

    /**
     * Set a property factory.
     *
     * @param  FactoryInterface $factory The property factory, to createable property values.
     * @return self
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property factory.
     *
     * @throws RuntimeException If the property factory was not previously set.
     * @return FactoryInterface
     */
    protected function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(sprintf(
                'Model [%s] does not have a property factory.',
                get_class($this)
            ));
        }

        return $this->propertyFactory;
    }

    /**
     * Retrieve the model's properties as a collection of {@see PropertyInterface} objects.
     *
     * @param  array $propertyIdents Optional. List of property identifiers
     *     for retrieving a subset of property objects.
     * @return PropertyInterface[]|\Generator
     */
    public function properties(array $propertyIdents = null)
    {
        // Hack: ensure metadata is loaded.
        $this->metadata();

        if ($propertyIdents === null) {
            $propertyIdents = array_keys($this->metadata()->properties());
        }

        if (empty($propertyIdents)) {
            return;
        }

        foreach ($propertyIdents as $propertyIdent) {
            yield $propertyIdent => $this->property($propertyIdent);
        }
    }

    /**
     * Retrieve an instance of {@see PropertyInterface} for the given property.
     *
     * @uses   DescribablePropertyTrait::createProperty() Called if the property has not been instantiated.
     * @param  string $propertyIdent The property identifier to return.
     * @throws InvalidArgumentException If the property identifier is not a string.
     * @return PropertyInterface The {@see PropertyInterface} if found, null otherwise
     */
    public function property($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string.'
            );
        }

        $metadata = $this->metadata();
        $property = $metadata->propertyObject($propertyIdent);
        if ($property === null) {
            $property = $this->createProperty($propertyIdent);
            $metadata->setPropertyObject($propertyIdent, $property);
        }

        return $property;
    }

    /**
     * Create an instance of {@see PropertyInterface} for the given property.
     *
     * @param  string $propertyIdent The property identifier to return.
     * @throws InvalidArgumentException If the $propertyIdent is not a string.
     * @throws RuntimeException If the requested property is invalid.
     * @return PropertyInterface The {@see PropertyInterface} if found, null otherwise
     */
    protected function createProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string.'
            );
        }

        $props = $this->metadata()->properties();

        if (empty($props)) {
            throw new RuntimeException(sprintf(
                'Invalid model metadata [%s] - No properties defined.',
                get_class($this)
            ));
        }

        if (!isset($props[$propertyIdent])) {
            throw new RuntimeException(sprintf(
                'Invalid model metadata [%s] - Undefined property metadata for "%s".',
                get_class($this),
                $propertyIdent
            ));
        }

        $propertyMetadata = $props[$propertyIdent];
        if (!isset($propertyMetadata['type'])) {
            throw new RuntimeException(sprintf(
                'Invalid model metadata [%s] - Undefined property type for "%s".',
                get_class($this),
                $propertyIdent
            ));
        }

        $factory  = $this->propertyFactory();
        $property = $factory->create($propertyMetadata['type']);
        $property->metadata();
        $property->setIdent($propertyIdent);
        $property->setData($propertyMetadata);

        return $property;
    }

    /**
     * Alias of {@see DescribablePropertyInterface::property()}
     * and {@see DescribablePropertyInterface::properties()}.
     *
     * @param  string|null $propertyIdent Optional property identifier.
     * @return PropertyInterface|PropertyInterface[] If $propertyIdent is NULL,
     *     returns all properties. Otherwise, returns the property associated with $propertyIdent.
     */
    public function p($propertyIdent = null)
    {
        if ($propertyIdent === null) {
            return $this->properties();
        }

        return $this->property($propertyIdent);
    }

    /**
     * Determine if the model has the given property.
     *
     * @param  string $propertyIdent The property identifier to lookup.
     * @throws InvalidArgumentException If the property identifier is not a string.
     * @return boolean
     */
    public function hasProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string.'
            );
        }

        $metadata   = $this->metadata();
        $properties = $metadata->properties();

        return isset($properties[$propertyIdent]);
    }
}
