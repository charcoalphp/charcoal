<?php

namespace Charcoal\Property;

use \InvalidArgumentException;
use \RuntimeException;

// From 'charcoal-factory'
use \Charcoal\Factory\FactoryInterface;

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
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new RuntimeException(
                'Model does not have a property factory.'
            );
        }

        return $this->propertyFactory;
    }

    /**
     * Retrieve the model's properties as a collection of `PropertyInterface` objects.
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
     * Retrieve an instance of `PropertyInterface` for the given property.
     *
     * @param string $propertyIdent The property identifier to return.
     * @throws InvalidArgumentException If the $propertyIdent is not a string.
     * @throws RuntimeException If the requested property is invalid.
     * @return PropertyInterface The \Charcoal\Model\Property if found, null otherwise
     */
    public function property($propertyIdent)
    {
        if (!is_string($propertyIdent)) {
            throw new InvalidArgumentException(
                'Property Ident must be a string.'
            );
        }

        $metadata = $this->metadata();
        $propertyObject = $metadata->propertyObject($propertyIdent);
        if ($propertyObject !== null) {
            $propertyValue = $this->propertyValue($propertyIdent);
            if ($propertyValue !== null || $propertyIdent === $this->key()) {
                $propertyObject->setVal($propertyValue);
            }

            return $propertyObject;
        }

        $props = $metadata->properties();

        if (empty($props)) {
            throw new RuntimeException(sprintf(
                'Invalid model metadata (%s) - No properties defined.',
                get_class($this)
            ));
        }

        if (!isset($props[$propertyIdent])) {
            throw new RuntimeException(
                sprintf('Invalid property: %s (not defined in metadata).', $propertyIdent)
            );
        }

        $propertyMetadata = $props[$propertyIdent];
        if (!isset($propertyMetadata['type'])) {
            throw new RuntimeException(
                sprintf('Invalid %s property: %s (type is undefined).', get_class($this), $propertyIdent)
            );
        }

        $factory  = $this->propertyFactory();
        $property = $factory->create($propertyMetadata['type']);
        $property->metadata();
        $property->setIdent($propertyIdent);
        $property->setData($propertyMetadata);

        $propertyValue = $this->propertyValue($propertyIdent);
        if ($propertyValue !== null || $propertyIdent === $this->key()) {
            $property->setVal($propertyValue);
        }

        $metadata->setPropertyObject($propertyIdent, $property);

        return $property;
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
                'Property Ident must be a string.'
            );
        }

        $metadata = $this->metadata();
        $props = $metadata->properties();

        return isset($props[$propertyIdent]);
    }

    /**
     * Retrieve the value of the given property.
     *
     * @param string $propertyIdent The property identifier to retrieve the value for.
     * @return mixed
     */
    abstract protected function propertyValue($propertyIdent);
}
