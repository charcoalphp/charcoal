<?php

namespace Charcoal\Source\Database;

use \DomainException as DomainException;

// Local parent namespace dependencies
use \Charcoal\Source\Order as Order;

/**
*
*/
class DatabaseOrder extends Order
{
    /**
    * @throws DomainException
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
                throw new DomainException('Values can not be empty.');
            }
            if ($property == '') {
                throw new DomainException('Property can not be empty.');
            }

            return 'FIELD(`'.$property.'`, '.implode(',', $values).')';
        } else {
            if ($property == '') {
                throw new DomainException('Property can not be empty.');
            }
            return '`'.$property.'` '.$mode;
        }
    }
}
