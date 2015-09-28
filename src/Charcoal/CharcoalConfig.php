<?php

namespace Charcoal;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Config\AbstractConfig;

use \Charcoal\Translation\TranslationConfig;

/**
* Main project configuration container
*
* The default data values is set from the JSON file locate at
* - `../../config/config.default.json`
*/
class CharcoalConfig extends AbstractConfig
{
    const DEFAULT_APPLICATION_ENV = 'live';

    /**
    * @var string $ROOT
    */
    public $ROOT;

    /**
    * @var string $URL
    */
    public $URL;

    /**
    * The path of the `admin` module, if set.
    * @var string $admin_path
    */
    private $admin_path;

    /**
    * The project name
    * @var string $project_name
    */
    private $project_name;

    /**
    * Extra debugging informations might be had when dev_mode is true.
    * @var boolean $dev_mode
    */
    private $dev_mode;

    /**
    * @var string $timezone
    */
    private $timezone;

    /**
    * Available database pool in project
    * @var array $databases
    * @see \Charcoal\Source\DatabaseSourceConfig
    */
    private $databases;
    /**
    * Default database identifier
    * @var string $default_database
    */
    private $default_database;

    /**
    * List of path where to search for metadata.
    * (ex: Model metadata as json config)
    * @var array $metadata_path
    */
    private $metadata_path = [];

    /**
    * List of path where to search for view templates.
    * (ex: templates, widgets and property inputs "mustache" templates)
    * @var array $template_path
    */
    private $template_path = [];

    /**
    * @var array|TranslationConfig $translation
    */
    private $translation;

    /**
    * The Config class is always extended with the default JSON config, from charcoal-core.
    *
    * @param mixed $config
    */
    public function __construct($config = null)
    {
        // Default data
        $this->add_file(__DIR__.'/../../config/config.default.json');

        parent::__construct($config);
    }



    /**
    * @return string
    */
    public function application_env()
    {
        $application_env = preg_replace('/!^[A-Za-z0-9_]+$/', '', getenv('APPLICATION_ENV'));
        if (!$application_env) {
            $application_env = self::DEFAULT_APPLICATION_ENV;
        }
        return $application_env;
    }

    /**
    * @param string $admin_path
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function set_admin_path($admin_path)
    {
        if (!is_string($admin_path)) {
            throw new InvalidArgumentException('Admin Path must be a string.');
        }
        $this->admin_path = $admin_path;
        return $this;
    }

    /**
    * @return string
    */
    public function admin_path()
    {
        return $this->admin_path;
    }

    /**
    * @param string $project_name
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function set_project_name($project_name)
    {
        if ($project_name === null) {
            $this->project_name = $null;
            return $this;
        }
        if (!is_string($project_name)) {
            throw new InvalidArgumentException('Project name must be a string');
        }
        $this->project_name = $project_name;
        return $this;
    }

    /**
    * @return string
    */
    public function project_name()
    {
        if ($this->project_name === null) {
            return $this->url();
        }
        return $this->project_name;
    }

    /**
    * @param boolean $dev_mode
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function set_dev_mode($dev_mode)
    {
        if (!is_bool($dev_mode)) {
            throw new InvalidArgumentException('Developer Mode must be a boolean.');
        }
        $this->dev_mode = $dev_mode;
        return $this;
    }

    /**
    * @return boolean
    */
    public function dev_mode()
    {
        return !!$this->dev_mode;
    }

    /**
    * @param string $timezone
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function set_timezone($timezone)
    {
        if (!is_string($timezone)) {
            throw new InvalidArgumentException('Timezone must be a string.');
        }
        $this->timezone = $timezone;
        return $this;
    }

    /**
    * @return string
    */
    public function timezone()
    {
        return $this->timezone;
    }

    /**
    * @param array $databases
    * @throws InvalidArgumentException
    * @return Config CHainable
    */
    public function set_databases($databases)
    {
        if (!is_array($databases)) {
            throw new InvalidArgumentException('Databases must be an array.');
        }
        $this->databases = $databases;
        return $this;
    }

    /**
    * @throws Exception
    * @return array
    */
    public function databases()
    {
        if ($this->databases === null) {
            throw new Exception('Databases are not set.');
        }
        return $this->databases;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException
    * @throws Exception
    * @return array
    */
    public function database_config($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Default database must be a string.');
        }
        $databases = $this->databases();
        if (!isset($databases[$ident])) {
            throw new Exception(sprintf('No database configuration matches "%s".', $ident));
        }
        return $databases[$ident];
    }

    /**
    * @param string $default_database
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function set_default_database($default_database)
    {
        if (!is_string($default_database)) {
            throw new InvalidArgumentException('Default database must be a string.');
        }
        $this->default_database = $default_database;
        return $this;
    }

    /**
    * @param string $ident
    * @param array  $config
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function add_database($ident, $config)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Database ident must be a string.');
        }
        if (!is_array($config)) {
            throw new InvalidArgumentException('Database config must be an array.');
        }

        if ($this->databases === null) {
            $this->databases = [];
        }
        $this->databases[$ident] = $config;
        return $this;
    }

    /**
    * @throws Exception
    * @return mixed
    */
    public function default_database()
    {
        if ($this->default_database === null) {
            throw new Exception('Default database is not set.');
        }
        return $this->default_database;
    }

    /**
    * @param array $metadata_path
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    * @todo Move to MetadataConfig
    */
    public function set_metadata_path($metadata_path)
    {
        if (!is_array($metadata_path)) {
            throw new InvalidArgumentException('Metadata path needs to be an array.');
        }
        $this->metadata_path = $metadata_path;
        return $this;
    }

    /**
    * @return array
    */
    public function metadata_path()
    {
        return $this->metadata_path;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function add_metadata_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path needs to be a string.');
        }

        $this->metadata_path[] = $path;
        return $this;
    }

    /**
    * @param array $template_path
    * @throws Exception
    * @return CharcoalConfig Chainable
    * @todo Move to ViewConfig
    */
    public function set_template_path($template_path)
    {
        if (!is_array($template_path)) {
            throw new Exception('Metadata Path needs to be an array.');
        }
        $this->template_path = $template_path;
        return $this;
    }

    /**
    * @return array
    */
    public function template_path()
    {
        return $this->template_path;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return CharcoalConfig Chainable
    */
    public function add_template_path($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('Path needs to be a string.');
        }

        $this->template_path[] = $path;
        return $this;
    }

    /**
    * @param array $translation
    * @return CharcoalConfig Chainable
    */
    public function set_translation($translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
    * @return TranslationConfig
    */
    public function translation()
    {
        $translation = new TranslationConfig();
        if (is_array($this->translation)) {
            $translation->set_data($this->translation);
        }
        $this->translation = $translation;
        return $this->translation;
    }
}
