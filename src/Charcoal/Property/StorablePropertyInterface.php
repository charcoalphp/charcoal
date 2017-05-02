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
     * @param mixed $val Optional. The value to convert to storage value.
     * @return mixed
     */
    public function storageVal($val);

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
