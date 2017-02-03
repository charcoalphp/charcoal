<?php

namespace Charcoal\Model;

/**
 * Model Interface
 */
interface ModelInterface
{
    /**
     * @param array $data The model data.
     * @return ModelInterface Chainable
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function data();

    /**
     * @param array $data The odel flat data.
     * @return ModelInterface Chainable
     */
    public function setFlatData(array $data);

    /**
     * @return array
     */
    public function flatData();

    /**
     * @return array
     */
    public function defaultData();

    /**
     * @return array
     */
    public function properties();

    /**
     * @param string $propertyIdent The property (ident) to get.
     * @return PropertyInterface
     */
    public function property($propertyIdent);

    /**
     * Alias of `properties()` (if not parameter is set) or `property()`.
     *
     * @param string $propertyIdent The property (ident) to get.
     * @return mixed
     */
    public function p($propertyIdent = null);
}
