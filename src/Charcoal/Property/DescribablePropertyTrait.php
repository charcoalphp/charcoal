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
                '[%s] Property identifier must be a string',
                get_class($this)
            );
        }

        $propertyIdent = $this->camelize($propertyIdent);

        $metadata = $this->metadata();
        $property = $metadata->propertyObject($propertyIdent);
        if ($property === null) {
            $property = $this->createProperty($propertyIdent);
            $metadata->setPropertyObject($propertyIdent, $property);
        }

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
                '[%s] Property identifier must be a string',
                get_class($this)
            );
        }

        $propertyIdent = $this->camelize($propertyIdent);

        $metadata   = $this->metadata();
        $properties = $metadata->properties();

        return isset($properties[$propertyIdent]);
    }

    /**
     * Filter the given metadata.
     *
     * This method gives a "change" to object to have some conditional logic on their property's metadata.
     *
     * @param  mixed  $propertyMetadata The property data from the described object.
     * @param  string $propertyIdent    The property identifier to return.
     * @return mixed Return the filtered $propertyMetadata.
     */
    public function filterPropertyMetadata($propertyMetadata, $propertyIdent)
    {
        unset($propertyIdent);
        // This method is a stub. Reimplement as needed in sub-classes.
        return $propertyMetadata;
    }

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
                '[%s] Model does not have a property factory',
                get_class($this)
            ));
        }

        return $this->propertyFactory;
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
                '[%s] Property identifier must be a string, received %s',
                get_class($this),
                (is_object($propertyIdent) ? get_class($propertyIdent) : gettype($propertyIdent))
            );
        }

        $propertyIdent = $this->camelize($propertyIdent);

        $props = $this->metadata()->properties();

        if (empty($props)) {
            throw new RuntimeException(sprintf(
                '[%s] Invalid model metadata - No properties defined (must define at least "%s")',
                get_class($this),
                $propertyIdent
            ));
        }

        if (!isset($props[$propertyIdent])) {
            throw new RuntimeException(sprintf(
                '[%s] Invalid model metadata - Undefined property metadata for "%s"',
                get_class($this),
                $propertyIdent
            ));
        }

        $propertyMetadata = $props[$propertyIdent];
        $propertyMetadata = $this->filterPropertyMetadata($propertyMetadata, $propertyIdent);
        if (!isset($propertyMetadata['type'])) {
            throw new RuntimeException(sprintf(
                '[%s] Invalid model metadata - Undefined property type for "%s"',
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
}
