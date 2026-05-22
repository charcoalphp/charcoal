<?php

namespace Charcoal\Property;

use InvalidArgumentException;

/**
 * Multi-Object Property holds references to external objects from different models.
 */
class MultiObjectProperty extends AbstractProperty
{
    private ?array $allowedTypes = null;

    private string $joinTable = 'charcoal_multi_objects';

    /**
     * @param array $types The allowed types map.
     * @return MultiObjectProperty Chainable
     */
    public function setAllowedTypes(array $types): static
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
    public function addAllowedType($type, array $typeOptions = []): static
    {
        $this->allowedTypes[$type] = $typeOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedTypes(): ?array
    {
        return $this->allowedTypes;
    }

    /**
     * @param string $table The join table.
     * @throws InvalidArgumentException If the table is not a string or contains invalid table characters.
     * @return MultiObjectProperty Chainable
     */
    public function setJoinTable($table): static
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException(
                'Join table must be a string'
            );
        }
        // For security reason, only alphanumeric characters (+ underscores) are valid table names.
        // Although SQL can support more, there's really no reason to.
        if (!preg_match('/\w/', $table)) {
            throw new InvalidArgumentException(
                sprintf('Table name "%s" is invalid: must be alphanumeric / underscore.', $table)
            );
        }
        $this->joinTable = $table;
        return $this;
    }

    public function getJoinTable(): string
    {
        return $this->joinTable;
    }

    /**
     * Create the join table on the database source, if it does not exist.
     */
    public function createJoinTable(): void
    {
        if ($this->joinTableExists()) {
            return;
        }

        $q = 'CREATE TABLE \'' . $this->getJoinTable() . '\' (
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

    public function joinTableExists(): bool
    {
        $q = 'SHOW TABLES LIKE \'' . $this->getJoinTable() . '\'';
        $this->logger->debug($q);
        $res = $this->source()->db()->query($q);
        $tableExists = $res->fetchColumn(0);

        return (bool)$tableExists;
    }

    public function type(): string
    {
        return 'multi-object';
    }

    /**
     * @return string|null
     */
    public function sqlType(): null
    {
        return null;
    }

    public function sqlPdoType(): int
    {
        return 0;
    }
}
