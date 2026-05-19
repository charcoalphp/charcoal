<?php

declare(strict_types=1);

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\SourceConfig;

/**
 * Database Config
 */
class DatabaseSourceConfig extends SourceConfig
{
    private ?string $type = null;

    private ?string $hostname = null;

    private ?string $username = null;

    private ?string $password = null;

    private ?string $database = null;

    private ?bool $disableUtf8 = null;

    #[\Override]
    public function defaults(): array
    {
        return [
            'type'         => 'mysql',
            'hostname'     => 'localhost',
            'username'     => '',
            'password'     => '',
            'database'     => '',
            'disable_utf8' => false,
        ];
    }

    /**
     * Set the database type.
     *
     * @param  string $type The database type.
     * @throws InvalidArgumentException If parameter is not a string.
     */
    #[\Override]
    public function setType($type): static
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Source type must be a string.'
            );
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Get the database type.
     *
     * @return string
     */
    #[\Override]
    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * Set the database hostname.
     *
     * @param  string $hostname The database server hostname.
     * @throws InvalidArgumentException If hostname is not a string.
     */
    public function setHostname($hostname): static
    {
        if (!is_string($hostname)) {
            throw new InvalidArgumentException(
                'Hostname must be a string.'
            );
        }
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * Get the database hostname.
     *
     * @return string
     */
    public function hostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * Set the database authentication identifier.
     *
     * @param  string $username The username.
     * @throws InvalidArgumentException If username is not a string.
     */
    public function setUsername($username): static
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException(
                'Username must be a string.'
            );
        }
        $this->username = $username;
        return $this;
    }

    /**
     * Get the database authentication identifier.
     *
     * @return string
     */
    public function username(): ?string
    {
        return $this->username;
    }

    /**
     * Set the database authentication password.
     *
     * @param  string $password The password.
     * @throws InvalidArgumentException If password is not a string.
     */
    public function setPassword($password): static
    {
        if (!is_string($password)) {
            throw new InvalidArgumentException(
                'Password must be a string.'
            );
        }
        $this->password = $password;
        return $this;
    }

    /**
     * Get the database authentication password.
     *
     * @return string
     */
    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * Set the database name.
     *
     * @param string $database The database name.
     * @throws InvalidArgumentException If database is not a string.
     */
    public function setDatabase($database): static
    {
        if (!is_string($database)) {
            throw new InvalidArgumentException(
                'Database must be a string.'
            );
        }
        $this->database = $database;
        return $this;
    }

    /**
     * Get the database name.
     *
     * @return string
     */
    public function database(): ?string
    {
        return $this->database;
    }

    /**
     * Set whether to disable UTF-8 compatibility or not.
     *
     * @param  boolean $disableUtf8 The disable flag.
     */
    public function setDisableUtf8($disableUtf8): static
    {
        $this->disableUtf8 = (bool) $disableUtf8;
        return $this;
    }

    /**
     * Get whether to disable UTF-8 compatibility or not.
     *
     * @return boolean
     */
    public function disableUtf8(): ?bool
    {
        return $this->disableUtf8;
    }
}
