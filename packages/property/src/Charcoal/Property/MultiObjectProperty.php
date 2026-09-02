<?php

namespace Charcoal\Property;

use InvalidArgumentException;

/**
 * Multi-Object Property holds references to external objects from different models.
 */
class MultiObjectProperty extends AbstractProperty
{
    /**
     * Allowlisted join-table identifier: letter/underscore, then alnum/underscore only.
     *
     * @var string
     */
    private const JOIN_TABLE_PATTERN = '/^[A-Za-z_][A-Za-z0-9_]*$/';

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

        $this->assertValidJoinTable($table);
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

        $table = $this->quoteIdentifier($this->getJoinTable());
        $q = 'CREATE TABLE ' . $table . ' (
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
        // LIKE pattern is a value (bindable). Escape metacharacters so `_` is literal.
        // Table names themselves cannot be bound as PDO parameters — only values can.
        $pattern = addcslashes($this->getJoinTable(), '%_\\');
        $q = 'SHOW TABLES LIKE ?';
        $this->logger->debug($q . ' [' . $pattern . ']');
        $sth = $this->source()->db()->prepare($q);
        $sth->execute([ $pattern ]);
        $tableExists = $sth->fetchColumn(0);

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

    /**
     * @param string $table The candidate join table name.
     * @throws InvalidArgumentException If the name is not a safe SQL identifier.
     * @return void
     */
    private function assertValidJoinTable($table)
    {
        // For security reason, only alphanumeric characters (+ underscores) are valid table names.
        // Although SQL can support more, there's really no reason to.
        // Anchors are required: a partial match would allow injection payloads that contain alnum chars.
        if (!preg_match(self::JOIN_TABLE_PATTERN, $table)) {
            throw new InvalidArgumentException(
                sprintf('Table name "%s" is invalid: must be alphanumeric / underscore.', $table)
            );
        }
    }

    /**
     * Quote a validated SQL identifier (MySQL-style backticks).
     *
     * Identifiers cannot be bound via PDO parameters; allowlisting + quoting is required.
     *
     * @param string $ident The identifier (must already pass assertValidJoinTable).
     * @return string
     */
    private function quoteIdentifier($ident)
    {
        $this->assertValidJoinTable($ident);

        return '`' . str_replace('`', '``', $ident) . '`';
    }
}
