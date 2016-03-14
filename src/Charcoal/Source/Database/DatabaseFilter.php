<?php

namespace Charcoal\Source\Database;

// Local parent namespace dependencies
use \Charcoal\Source\Filter;

/**
* The DatabaseFilter makes a Filter SQL-aware.
*/
class DatabaseFilter extends Filter
{
    /**
    * Get the filter's SQL string to append to a "WHERE" clause.
    *
    * @return string
    */
    public function sql()
    {
        if ($this->string) {
            return $this->string;
        }
        $fields = $this->sqlFields();
        if (empty($fields)) {
            return '';
        }

        $filter = '';

        foreach ($fields as $field) {
            $val = $this->val();

            // Support custom "operator" for the filter
            $operator = $this->operator();

            // Support for custom function on column name
            $function = $this->func();

            if ($function) {
                $target = sprintf('%1$s(`%2$s`)', $function, $field);
            } else {
                $target = sprintf('`%s`', $field);
            }

            switch ($operator) {
                case 'FIND_IN_SET':
                    $filter .= sprintf('%1$s(\'%2$s\', %3$s)', $operator, $val, $target);
                    break;

                case 'IS NULL':
                case 'IS NOT NULL':
                    $filter .= sprintf('(%1$s %2$s)', $target, $operator);
                    break;

                case 'IN':
                    if (is_array($val)) {
                        $val = '\''.implode('\',\'', $val).'\'';
                    }

                    $filter .= sprintf('(%1$s %2$s (%3$s))', $target, $operator, $val);
                    break;

                default:
                    $filter .= sprintf('(%1$s %2$s \'%3$s\')', $target, $operator, $val);
                    break;
            }
        }

        return $filter;
    }

    /**
    * @return array
    */
    private function sqlFields()
    {
        $property = $this->property();
        if ($property) {
            /** @todo Load Property from associated model metadata. */
            return [$property];
        }
        /*
        $field = $this->field();
        if($field) {
        return [$field];
        }
        */
        return [];
    }
}
