<?php

namespace Charcoal\Property;

use \InvalidArgumentException;
use \RuntimeException;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

// From 'charcoal-property'
use \Charcoal\Property\PropertyInterface;

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
     * @todo   [mcaskill 2016-09-16] Move factory setter to classes using this trait.
     * @param  FactoryInterface $factory The property factory, to createable property values.
     * @return DescribableInterface
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the property factory.
     *
     * @todo   [mcaskill 2016-09-16] Move factory getter to classes using this trait.
     *     Redefine this method as an abstract method.
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
     * @return PropertyInterface[]|\Generator
     */
    public function properties()
    {
        // Hack: ensure metadata is loaded.
        $this->metadata();

        $props = array_keys($this->metadata()->properties());

        if (empty($props)) {
            return;
        }

        foreach ($props as $propertyIdent) {
            yield $propertyIdent => $this->property($propertyIdent);
        }
    }

    /**
     * Retrieve an instance of {@see PropertyInterface} for the given property.
     *
     * @uses   DescribablePropertyTrait::createProperty() Called if the property has not been instantiated.
     * @param  string $propertyIdent The property identifier to return.
     * @throws InvalidArgumentException If the $propertyIdent is not a string.
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
        $propertyMetadata = $this->filterPropertyMetadata($propertyMetadata, $propertyIdent);
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
     * Filter the given metadata.
     *
     * @param  mixed  $propertyMetadata The property data from the described object.
     * @param  string $propertyIdent    The property identifier to return.
     * @return mixed Return the filtered $propertyMetadata.
     */
    public function filterPropertyMetadata($propertyMetadata, $propertyIdent)
    {
        return $propertyMetadata;
    }

    /**
     * Alias of {@see DescribablePropertyInterface::property()}
     * and {@see DescribablePropertyInterface::properties()},
     * depending if argument is set or not.
     *
     * Shortcut for:
     *
     * - `property()` if the first parameter is set,
     * - `properties()` if the $propertyIdent is not set (NULL)
     *
     * @param string $propertyIdent The property ident to return.
     * @return array|PropertyInterface
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
     * @throws InvalidArgumentException If the identifier argument is not a string.
     * @return boolean
     */
    public function hasProperty($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property identifier must be a string.'
            );
        }

        $metadata = $this->metadata();
        $props = $metadata->properties();

        return isset($props[$propertyIdent]);
    }
}
