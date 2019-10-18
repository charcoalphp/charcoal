<?php

namespace Charcoal\Property;

/**
 *
 */
interface StorablePropertyInterface
{
    /**
     * @param  mixed $val The value to set as field value.
     * @return \Charcoal\Property\PropertyField[]
     */
    public function fields($val = null);

    /**
     * Retrieve the property's identifier formatted for field names.
     *
     * @param  string|null $key The field key to suffix to the property identifier.
     * @return string
     */
    public function fieldIdent($key = null);

    /**
     * @return string[]
     */
    public function fieldNames();

    /**
     * Retrieve the property's value in a format suitable for storage.
     *
     * @param  mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val);

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param  string|null $encoding The encoding identifier or SQL encoding and collation.
     * @return self
     */
    public function setSqlEncoding($encoding);

    /**
     * Retrieve the property's SQL encoding & collation.
     *
     * @return string|null
     */
    public function sqlEncoding();

    /**
     * @return string|null
     */
    public function sqlExtra();

    /**
     * @return string
     */
    public function sqlType();

    /**
     * @return integer
     */
    public function sqlPdoType();
}
