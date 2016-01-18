<?php

namespace Charcoal\Source;

// Dependencies from `PHP`
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Encoder\EncoderFactory;

// Local namespace dependencies
use \Charcoal\Source\SourceConfig;

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
    * @var string $passwordEncoding
    */
    private $passwordEncoding;
    /**
    * @var string $passwordSalt
    */
    private $passwordSalt;
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
            'passwordEncoding' => '',
            'passwordSalt'     => '',
            'database'          => '',
            'table'             => '',
            'disableUtf8'      => false
        ];
    }

    /**
    * Set hostname
    *
    * @param string $hostname
    * @throws InvalidArgumentException if hostname is not a string
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
    * @param string $username
    * @throws InvalidArgumentException if username is not a string
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
    * @param string $password
    * @throws InvalidArgumentException if password is not a string
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
        if ($this->passwordEncoding()) {
            $factory = new EncoderFactory();
            $encoder = $factory->get($this->passwordEncoding());

            $this->password = $encoder->decode($this->password, $this->passwordSalt());
            $this->passwordEncoding = null;
            $this->passwordSalt = null;
        }
        return $this->password;
    }

    /**
    * Set password encoding type
    *
    * @param string $passwordEncoding Must be a valid `Encoder` type
    * @throws InvalidArgumentException if password is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function setPasswordEncoding($passwordEncoding)
    {
        if (!is_string($passwordEncoding)) {
            throw new InvalidArgumentException(
                'Password Encoding must be a string.'
            );
        }
        $this->passwordEncoding = $passwordEncoding;
        return $this;
    }

    /**
    * Get password encoding type
    *
    * @return string
    */
    public function passwordEncoding()
    {
        return $this->passwordEncoding;
    }

    /**
    * Set password salt, if using encoding
    *
    * @param string $passwordSalt
    * @throws InvalidArgumentException if password is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function setPasswordSalt($passwordSalt)
    {
        if (!is_string($passwordSalt)) {
            throw new InvalidArgumentException(
                'Password Salt must be a string.'
            );
        }
        $this->passwordSalt = $passwordSalt;
        return $this;
    }

    /**
    * Get password salt (optional), if using encoding
    *
    * @return string
    */
    public function passwordSalt()
    {
        return $this->passwordSalt;
    }

    /**
    * Set database
    *
    * @param string $database
    * @throws InvalidArgumentException if database is not a string
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
    * @param boolean $disableUtf8
    * @throws InvalidArgumentException if disableUtf8 is not a boolean
    * @return DatabaseSourceConfig Chainable
    */
    public function setDisableUtf8($disableUtf8)
    {
        if (!is_bool($disableUtf8)) {
            throw new InvalidArgumentException(
                'Disable UTF8 must be a boolean.'
            );
        }
        $this->disableUtf8 = $disableUtf8;
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
