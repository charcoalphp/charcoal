<?php

namespace Charcoal\Source\Database;

// Dependencies from `PHP`
use \DomainException;

// Local parent namespace dependencies
use \Charcoal\Source\Order;

/**
 * The DatabaseOrder makes a Order SQL-aware.
 */
class DatabaseOrder extends Order
{
    /**
     * Get the order's SQL string to append to an "ORDER BY" subquery.
     *
     * There are 4 modes of "Order":
     * - `asc` to order in ascending (A-Z / 0-9) order.
     * - `desc` to order in descending (Z-A / 9-0) order.
     * - `rand` to order in a random fashion.
     * - `values` to order by a defined array of properties.
     *
     * @throws DomainException If any required property is empty.
     * @return string
     */
    public function sql()
    {
        $property = $this->property();
        $mode = $this->mode();

        if ($mode == 'rand') {
            return 'RAND()';
        }
        if ($mode == 'values') {
            $values = $this->values();
            if (empty($values)) {
                throw new DomainException(
                    'Values can not be empty.'
                );
            }

            if ($property == '') {
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

            return 'FIELD(`'.$property.'`, '.implode(',', $values).')';
        } else {
            if ($property == '') {
                throw new DomainException(
                    'Property can not be empty.'
                );
            }
            return '`'.$property.'` '.$mode;
        }
    }
}
