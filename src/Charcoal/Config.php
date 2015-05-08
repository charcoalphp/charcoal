<?php

namespace Charcoal;

use \Charcoal\Config\AbstractConfig as AbstractConfig;

class Config extends AbstractConfig implements \ArrayAccess
{
    const DEFAULT_APPLICATION_ENV = 'live';

    public $ROOT;
    public $URL;

    private $_project_name;
    private $_dev_mode;

    private $_timezone;

    public $_salt;

    public $cache;

    private $_databases;
    private $_default_database;

    private $_metadata_path = [];
    private $_template_path = [];

    public function __construct($config = null)
    {
        // Default data
        $this->add_file(__DIR__.'/../../config/config.default.json');

        parent::__construct($config);

    }

    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
        unset($this->{$offset});
    }

    public function set_data($data)
    {
        if (isset($data['dev_mode'])) {
            $this->set_dev_mode($data['dev_mode']);
            unset($data['dev_mode']);
        }
        if (isset($data['timezone'])) {
            $this->set_timezone($data['timezone']);
            unset($data['timezone']);
        }

        if (isset($data['databases'])) {
            $this->set_databases($data['databases']);
            unset($data['databases']);
        }
        if (isset($data['default_database'])) {
            $this->set_default_database($data['default_database']);
            unset($data['default_database']);
        }
        if (isset($data['metadata_path'])) {
            $this->set_metadata_path($data['metadata_path']);
            unset($data['metadata_path']);
        }
        if (isset($data['template_path'])) {
            $this->set_metadata_path($data['template_path']);
            unset($data['template_path']);
        }

        foreach ($data as $k => $v) {
            $this->{$k} = $v;
        }

        return $this;
    }

    public function project_name()
    {
        return $this->project_name;
    }

    public function salt()
    {
        return $this->_salt;
    }

    public function application_env()
    {
        $application_env = preg_replace('/!^[A-Za-z0-9_]+$/', '', getenv('APPLICATION_ENV'));
        if (!$application_env) {
            $application_env = self::DEFAULT_APPLICATION_ENV;
        }
        return $application_env;
    }

    public function set_dev_mode($dev_mode)
    {
        if (!is_bool($dev_mode)) {
            throw new \InvalidArgumentException('Dev mode must be a boolean.');
        }
        $this->_dev_mode = $dev_mode;
        return $this;
    }

    public function dev_mode()
    {
        return !!$this->_dev_mode;
    }

    public function set_timezone($timezone)
    {
        if (!is_string($timezone)) {
            throw new \InvalidArgumentException('Timezone must be a string.');
        }
        $this->_timezone = $timezone;
        return $this;
    }

    public function timezone()
    {
        return $this->_timezone;
    }

    public function set_databases($databases)
    {
        if (!is_array($databases)) {
            throw new \InvalidArgumentException('Databases must be an array.');
        }
        $this->_databases = $databases;
        return $this;
    }
    
    public function databases()
    {
        if ($this->_databases == null) {
            throw new \Exception('Databases are not set');
        }
        return $this->_databases;
    }

    public function database_config($ident)
    {
        if (!is_string($ident)) {
            throw new \InvalidArgumentException('Default database must be a string.');
        }
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new \Exception(sprintf('No database configuration matches "%s"', $ident));
        }
        return $databases[$ident];
    }

    public function set_default_database($default_database)
    {
        if (!is_string($default_database)) {
            throw new \InvalidArgumentException('Default database must be a string.');
        }
        $this->_default_database = $default_database;
    }

    public function add_database($ident, $config)
    {
        if (!is_string($ident)) {
            throw new \InvalidArgumentException('Database ident must be a string.');
        }
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Database config must be an array.');
        }

        if ($this->_databases === null) {
            $this->_databases = [];
        }
        $this->_databases[$ident] = $config;
        return $this;
    }

    public function default_database()
    {
        if ($this->_default_database == null) {
            throw new \Exception('Default database is not set.');
        }
        return $this->_default_database;
    }

    public function set_metadata_path($metadata_path)
    {
        if (!is_array($metadata_path)) {
            throw new \Exception('Metadata path needs to be an array');
        }
        $this->_metadata_path = $metadata_path;
        return $this;
    }

    public function metadata_path()
    {
        return $this->_metadata_path;
    }

    public function add_metadata_path($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path needs to be a string');
        }

        $this->_metadata_path[] = $path;
        return $this;
    }

    public function set_template_path($template_path)
    {
        if (!is_array($template_path)) {
            throw new \Exception('Metadata path needs to be an array');
        }
        $this->_template_path = $template_path;
        return $this;
    }

    public function template_path()
    {
        return $this->_template_path;
    }

    public function add_template_path($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path needs to be a string');
        }

        $this->_template_path[] = $path;
        return $this;
    }
}
