<?php

declare(strict_types=1);

namespace Charcoal\Property;

/**
 *
 */
interface StorablePropertyInterface
{
    /**
     * @param mixed|null $val The value to set as field value.
     * @return PropertyField[]
     */
    public function fields(mixed $val = null): array;

    /**
     * Retrieve the property's identifier formatted for field names.
     *
     * @param string|null $key The field key to suffix to the property identifier.
     */
    public function fieldIdent(?string $key = null): string;

    /**
     * @return string[]
     */
    public function fieldNames(): array;

    /**
     * Retrieve the property's value in a format suitable for storage.
     *
     * @param  mixed $val Optional. The value to convert to storage value.
     */
    public function storageVal(mixed $val): mixed;

    /**
     * Set the property's SQL encoding & collation.
     *
     * @param string|null $encoding The encoding identifier or SQL encoding and collation.
     */
    public function setSqlEncoding(?string $encoding): StorablePropertyInterface;

    /**
     * Retrieve the property's SQL encoding & collation.
     */
    public function sqlEncoding(): ?string;
    public function sqlExtra(): ?string;
    public function sqlType(): ?string;
    public function sqlPdoType(): int;
}
