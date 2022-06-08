<?php

namespace Charcoal\Property;

use InvalidArgumentException;

/**
 * Multi-Object Property holds references to external objects from different models.
 */
class MultiObjectProperty extends AbstractProperty
{
    /**
     * @var array $allowedTypes
     */
    private $allowedTypes;

    /**
     * @var boolean $groupedByType
     */
    private $groupedByType = true;

    /**
     * @var string $joinTable
     */
    private $joinTable = 'charcoal_multi_objects';

    /**
     * @param array $types The allowed types map.
     * @return MultiObjectProperty Chainable
     */
    public function setAllowedTypes(array $types)
    {
        foreach ($types as $type => $typeOptions) {
            $this->addAllowedType($type, $typeOptions);
        }
        return $this;
    }

    /**
     * @param string $type        The (allowed) object type.
     * @param array  $typeOptions Extra options for the type.
     * @return MultiObjectProperty Chainable
     */
    public function addAllowedType($type, array $typeOptions = [])
    {
        $this->allowedTypes[$type] = $typeOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return $this->allowedTypes;
    }

    /**
     * @param string $table The join table.
     * @throws InvalidArgumentException If the table is not a string or contains invalid table characters.
     * @return MultiObjectProperty Chainable
     */
    public function setJoinTable($table)
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException(
                'Join table must be a string'
            );
        }
        // For security reason, only alphanumeric characters (+ underscores) are valid table names.
        // Although SQL can support more, there's really no reason to.
        if (!preg_match('/[A-Za-z0-9_]/', $table)) {
            throw new InvalidArgumentException(
                sprintf('Table name "%s" is invalid: must be alphanumeric / underscore.', $table)
            );
        }
        $this->joinTable = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getJoinTable()
    {
        return $this->joinTable;
    }

    /**
     * Create the join table on the database source, if it does not exist.
     *
     * @return void
     */
    public function createJoinTable()
    {
        if ($this->joinTableExists() === true) {
            return;
        }

        $q = 'CREATE TABLE \''.$this->getJoinTable().'\' (
            target_type VARCHAR(255),
            target_id VARCHAR(255),
            target_property VARCHAR(255),
            attachment_type VARCHAR(255),
            attachment_id VARCHAR(255),
            created DATETIME
        )';
        $this->logger->debug($q);
        $this->source()->db()->query($q);
    }

    /**
     * @return boolean
     */
    public function joinTableExists()
    {
        $q = 'SHOW TABLES LIKE \''.$this->getJoinTable().'\'';
        $this->logger->debug($q);
        $res = $this->source()->db()->query($q);
        $tableExists = $res->fetchColumn(0);

        return !!$tableExists;
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'multi-object';
    }

    /**
     * @return string|null
     */
    public function sqlType()
    {
        return null;
    }

    /**
     * @return integer
     */
    public function sqlPdoType()
    {
        return 0;
    }
}
