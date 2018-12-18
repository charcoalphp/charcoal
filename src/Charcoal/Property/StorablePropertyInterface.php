<?php

namespace Charcoal\Property;

/**
 *
 */
interface StorablePropertyInterface
{

    /**
     * @param  mixed $val The value to set as field value.
     * @return array
     */
    public function fields($val);

    /**
     * @return array
     */
    public function fieldNames();

    /**
     * @param mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val);

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param  string $ident The encoding ident.
     * @throws \InvalidArgumentException  If the identifier is not a string.
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
