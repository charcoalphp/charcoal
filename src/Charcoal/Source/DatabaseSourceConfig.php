<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use InvalidArgumentException;

// Local namespace dependencies
use Charcoal\Source\SourceConfig;

/**
 *
 */
class DatabaseSourceConfig extends SourceConfig
{
    /**
     * @var string $hostname
     */
    private $hostname;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var string $database
     */
    private $database;


    /**
     * @var boolean $disableUtf8
     */
    private $disableUtf8;

    /**
     * @return array
     */
    public function defaults()
    {
        return [
            'type'              => 'mysql',
            'hostname'          => 'localhost',
            'username'          => '',
            'password'          => '',
            'database'          => '',
            'table'             => '',
            'disable_ytf8'      => false
        ];
    }

    /**
     * Set hostname
     *
     * @param string $hostname The database hostname.
     * @throws InvalidArgumentException If hostname is not a string.
     * @return DatabaseSourceConfig Chainable
     */
    public function setHostname($hostname)
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
     * Get hostname
     *
     * @return string
     */
    public function hostname()
    {
        return $this->hostname;
    }

    /**
     * Set username
     *
     * @param string $username The database username.
     * @throws InvalidArgumentException If username is not a string.
     * @return DatabaseSourceConfig Chainable
     */
    public function setUsername($username)
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
     * Get username
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password The database password.
     * @throws InvalidArgumentException If password is not a string.
     * @return DatabaseSourceConfig Chainable
     */
    public function setPassword($password)
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
     * Get password
     *
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * Set database
     *
     * @param string $database The database name.
     * @throws InvalidArgumentException If database is not a string.
     * @return DatabaseSourceConfig Chainable
     */
    public function setDatabase($database)
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
     * Get database
     *
     * @return string
     */
    public function database()
    {
        return $this->database;
    }

    /**
     * @param boolean $disableUtf8 The "disable UTF8" flag.
     * @return DatabaseSourceConfig Chainable
     */
    public function setDisableUtf8($disableUtf8)
    {
        $this->disableUtf8 = !!$disableUtf8;
        return $this;
    }

    /**
     * @return bools
     */
    public function disableUtf8()
    {
        return $this->disableUtf8;
    }
}
