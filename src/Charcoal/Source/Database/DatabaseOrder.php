<?php

namespace Charcoal\Source\Database;

use DomainException;

// From 'charcoal-core'
use Charcoal\Source\Order;

/**
 * The DatabaseOrder makes a Order SQL-aware.
 */
class DatabaseOrder extends Order
{
    /**
     * Retrieve the Order's SQL string to append to an ORDER BY clause.
     *
     * @throws DomainException If any required property is empty.
     * @return string
     */
    public function sql()
    {
        $mode = $this->mode();
        switch ($mode) {
            case self::MODE_RANDOM:
                return $this->byRandom();

            case self::MODE_VALUES:
                return $this->byValues();

            case self::MODE_CUSTOM:
                return $this->byCustom();
        }

        $property = $this->property();
        if (empty($property)) {
            throw new DomainException(
                'Property can not be empty.'
            );
        }

        return sprintf('`%1$s` %2$s', $property, $mode);
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_RANDOM} mode.
     *
     * @return string
     */
    private function byRandom()
    {
        return 'RAND()';
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_CUSTOM} mode.
     *
     * @return string
     */
    private function byCustom()
    {
        $sql = $this->string();
        if ($sql) {
            return $sql;
        }
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_VALUES} mode.
     *
     * @throws DomainException If any required property or values is empty.
     * @return string
     */
    private function byValues()
    {
        $values = $this->values();
        if (empty($values)) {
            throw new DomainException(
                'Values can not be empty.'
            );
        }

        $property = $this->property();
        if (empty($property)) {
            throw new DomainException(
                'Property can not be empty.'
            );
        }

        $values = array_filter($values, 'is_scalar');
        $values = array_map(
            function ($val) {
                if (!is_numeric($val)) {
                    $val = htmlspecialchars($val, ENT_QUOTES);
                    $val = sprintf('"%s"', $val);
                }

                return $val;
            },
            $values
        );

        return sprintf('FIELD(`%1$s`, %2$s)', $property, implode(',', $values));
    }
}
