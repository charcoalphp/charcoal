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
    public function fields($val);

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
     * @param  string $ident The encoding ident.
     * @return self
     */
    public function setSqlEncoding($ident);

    /**
     * @return string
     */
    public function sqlEncoding();

    /**
     * @return string
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
