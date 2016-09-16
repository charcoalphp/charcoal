<?php

namespace Charcoal\Property;

use \Charcoal\Model\DescribableInterface;

/**
 * Defines a model having attributes that allow the formatting of its data.
 */
interface DescribablePropertyInterface extends DescribableInterface
{
    /**
     * Retrieve the model's properties as a collection of `PropertyInterface` objects.
     *
     * @return array
     */
    public function properties();

    /**
     * Retrieve an instance of `PropertyInterface` for the given property.
     *
     * @param string $propertyIdent The property identifier to return.
     * @return PropertyInterface
     */
    public function property($propertyIdent);

    /**
     * Alias of {@see self::property()} and {@see self::properties()},
     * depending if argument is set or not.
     *
     * @param mixed $propertyIdent Property ident, if null, return all properties.
     * @return array|PropertyInterface|null
     */
    public function p($propertyIdent = null);
}
