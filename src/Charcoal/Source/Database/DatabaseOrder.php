<?php

namespace Charcoal\Source\Database;

use DomainException;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Order;

/**
 * The DatabaseOrder makes a Order SQL-aware.
 */
class DatabaseOrder extends Order
{
    /**
     * The table related to the field identifier.
     *
     * @var string $tableName
     */
    protected $tableName = DatabaseSource::DEFAULT_TABLE_ALIAS;

    /**
     * Retrieve the default values for sorting.
     *
     * @return array
     */
    public function defaultData()
    {
        $defaults = parent::defaultData();
        $defaults['table_name'] = DatabaseSource::DEFAULT_TABLE_ALIAS;

        return $defaults;
    }

    /**
     * Retrieve the Order's SQL string to append to an ORDER BY clause.
     *
     * @throws DomainException If any required property is empty.
     * @return string
     */
    public function sql()
    {
        if ($this->active() === false) {
            return '';
        }

        $mode = $this->mode();
        switch ($mode) {
            case self::MODE_RANDOM:
                return $this->byRandom();

            case self::MODE_VALUES:
                return $this->byValues();

            case self::MODE_CUSTOM:
                return $this->byExpression();

            case self::MODE_ASC:
            case self::MODE_DESC:
                $mode = strtoupper($mode);
                break;
        }

        $fields = $this->fieldIdentifiers();
        if (empty($fields)) {
            return '';
        }

        $clauses = [];
        foreach ($fields as $fieldName) {
            $clauses[] = sprintf('%1$s %2$s', $fieldName, $mode);
        }

        return implode(', ', $clauses);
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
    private function byExpression()
    {
        $sql = $this->string();
        if ($sql) {
            $sql = strtr($sql, [
                '{mode}'       => $this->mode(),
                '{values}'     => $this->prepareValues($this->values()),
                '{property}'   => $this->property(),
                '{fielfName}'  => $this->fieldIdentifier(),
                '{tableName}'  => $this->tableName(),
            ]);
        }

        return $sql;
    }

    /**
     * Retrieve the ORDER BY clause for the {@see self::MODE_VALUES} mode.
     *
     * @throws DomainException If any required property or values is empty.
     * @return string
     */
    private function byValues()
    {
        $fieldName = $this->fieldIdentifier();
        if (empty($fieldName)) {
            throw new DomainException(
                'Field Name can not be empty.'
            );
        }

        $values = $this->values();
        if (empty($values)) {
            throw new DomainException(
                'Values can not be empty.'
            );
        }

        $values = $this->prepareValues($values);

        return sprintf('FIELD(%1$s, %2$s)', $fieldName, implode(',', $values));
    }

    /**
     * Parse the given values for SQL.
     *
     * @param  mixed $values The value to be normalized.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return array Returns a collection of parsed values.
     */
    public function prepareValues($values)
    {
        if (empty($values)) {
            return [];
        }

        $values = array_filter($values, 'is_scalar');
        $values = array_map(
            function ($val) {
                $val = $this::parseValue($val);
                if (!is_numeric($val)) {
                    $val = htmlspecialchars($val, ENT_QUOTES);
                    $val = sprintf('"%s"', $val);
                }

                return $val;
            },
            $values
        );

        return $values;
    }
}
