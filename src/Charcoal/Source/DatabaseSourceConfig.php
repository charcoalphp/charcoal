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
    * @var string $password_encoding
    */
    private $password_encoding;
    /**
    * @var string $password_salt
    */
    private $password_salt;
    /**
    * @var string $database
    */
    private $database;

    /**
    * @var boolean $disable_utf8
    */
    private $disable_utf8;

    /**
    * @return array
    */
    public function default_data()
    {
        return [
            'type'              => 'mysql',
            'hostname'          => 'localhost',
            'username'          => null,
            'password'          => '',
            'password_encoding' => null,
            'password_salt'     => null,
            'database'          => null,
            'table'             => '',
            'disable_utf8'      => false
        ];
    }

    /**
    * @param array $data
    * @return DatabaseSourceConfig Chainable
    */
    public function set_data(array $data)
    {
        parent::set_data($data);

        if (isset($data['hostname']) && $data['hostname'] !== null) {
            $this->set_hostname($data['hostname']);
        }
        if (isset($data['username']) && $data['username'] !== null) {
            $this->set_username($data['username']);
        }
        if (isset($data['password']) && $data['password'] !== null) {
            $this->set_password($data['password']);
        }
        if (isset($data['database']) && $data['database'] !== null) {
            $this->set_database($data['database']);
        }
        if (isset($data['disable_utf8']) && $data['disable_utf8'] !== null) {
            $this->set_disable_utf8($data['disable_utf8']);
        }
        return $this;
    }

    /**
    * Set hostname
    *
    * @param string $hostname
    * @throws InvalidArgumentException if hostname is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function set_hostname($hostname)
    {
        if (!is_string($hostname)) {
            throw new InvalidArgumentException('Hostname must be a string.');
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
    public function set_username($username)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException('Username must be a string.');
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
    public function set_password($password)
    {
        if (!is_string($password)) {
            throw new InvalidArgumentException('Password must be a string.');
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
        if ($this->password_encoding()) {
            $factory = new EncoderFactory();
            $encoder = $factory->get($this->password_encoding());

            $this->password = $encoder->decode($this->password, $this->password_salt());
            $this->password_encoding = null;
            $this->password_salt = null;
        }
        return $this->password;
    }

    /**
    * Set password encoding type
    *
    * @param string $password_encoding Must be a valid `Encoder` type
    * @throws InvalidArgumentException if password is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function set_password_encoding($password_encoding)
    {
        if (!is_string($password_encoding)) {
            throw new InvalidArgumentException('Password Encoding must be a string.');
        }
        $this->password_encoding = $password_encoding;
        return $this;
    }

    /**
    * Get password encoding type
    *
    * @return string
    */
    public function password_encoding()
    {
        return $this->password_encoding;
    }

    /**
    * Set password salt, if using encoding
    *
    * @param string $password_salt
    * @throws InvalidArgumentException if password is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function set_password_salt($password_salt)
    {
        if (!is_string($password_salt)) {
            throw new InvalidArgumentException('Password Salt must be a string.');
        }
        $this->password_salt = $password_salt;
        return $this;
    }

    /**
    * Get password salt (optional), if using encoding
    *
    * @return string
    */
    public function password_salt()
    {
        return $this->password_salt;
    }

    /**
    * Set database
    *
    * @param string $database
    * @throws InvalidArgumentException if database is not a string
    * @return DatabaseSourceConfig Chainable
    */
    public function set_database($database)
    {
        if (!is_string($database)) {
            throw new InvalidArgumentException('Database must be a string.');
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
    * @param boolean $disable_utf8
    * @throws InvalidArgumentException if disable_utf8 is not a boolean
    * @return DatabaseSourceConfig Chainable
    */
    public function set_disable_utf8($disable_utf8)
    {
        if (!is_bool($disable_utf8)) {
            throw new InvalidArgumentException('Disable UTF8 must be a boolean.');
        }
        $this->disable_utf8 = $disable_utf8;
        return $this;
    }

    /**
    * @return bools
    */
    public function disable_utf8()
    {
        return $this->disable_utf8;
    }
}
