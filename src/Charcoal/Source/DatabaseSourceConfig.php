<?php

namespace Charcoal\Source;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Source\SourceConfig as SourceConfig;

use \Charcoal\Encoder\EncoderFactory as EncoderFactory;

class DatabaseSourceConfig extends SourceConfig
{
    /**
    * @var string $_hostname
    */
    private $_hostname;
    /**
    * @var string $_username
    */
    private $_username;
    /**
    * @var string $_password
    */
    private $_password;
    /**
    * @var string $_password_encoding
    */
    private $_password_encoding;
    /**
    * @var string $_password_salt
    */
    private $_password_salt;
    /**
    * @var string $_database
    */
    private $_database;

    /**
    * @var boolean $_disable_utf8
    */
    private $_disable_utf8;

    /**
    * @return array
    */
    public function default_data()
    {
        return [
            'type'          => 'mysql',
            'hostname'      => 'localhost',
            'username'      => null,
            'password'      => '',
            'password_encoding' => null,
            'password_salt' => null,
            'database'      => null,
            'table'         => '',
            'disable_utf8'  => false
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_hostname = $hostname;
        return $this;
    }

    /**
    * Get hostname
    *
    * @return string
    */
    public function hostname()
    {
        return $this->_hostname;
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_username = $username;
        return $this;
    }

    /**
    * Get username
    *
    * @return string
    */
    public function username()
    {
        return $this->_username;
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_password = $password;
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
            $encoder = EncoderFactory::instance()->get($this->password_encoding());
        
            $this->_password = $encoder->decode($this->_password, $this->password_salt());
            $this->_password_encoding = null;
            $this->_password_salt = null;
        }
        return $this->_password;
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_password_encoding = $password_encoding;
        return $this;
    }

    /**
    * Get password encoding type
    *
    * @return string
    */
    public function password_encoding()
    {
        return $this->_password_encoding;
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_password_salt = $password_salt;
        return $this;
    }

    /**
    * Get password salt (optional), if using encoding
    *
    * @return string
    */
    public function password_salt()
    {
        return $this->_password_salt;
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
            throw new InvalidArgumentException('Parameter must be a string');
        }
        $this->_database = $database;
        return $this;
    }

    /**
    * Get database
    *
    * @return string
    */
    public function database()
    {
        return $this->_database;
    }

    /**
    * @param bool $disable_utf8
    * @throws InvalidArgumentException if disable_utf8 is not a bool
    * @return DatabaseSourceConfig Chainable
    */
    public function set_disable_utf8($disable_utf8)
    {
        if (!is_bool($disable_utf8)) {
            throw new InvalidArgumentException('Parameter must be a boolean');
        }
        $this->_disable_utf8 = $disable_utf8;
        return $this;
    }

    /**
    * @return bools
    */
    public function disable_utf8()
    {
        return $this->_disable_utf8;
    }
}
