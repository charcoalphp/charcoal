<?php

namespace Charcoal\Source\Database;

use \Charcoal\Source\SourceConfig as SourceConfig;

class SourceDatabaseConfig extends SourceConfig
{
    public $db_type;
    
    public $hostname;
    public $port;
    public $username;
    public $password;

    public $password_encoding;
    public $password_salt;
}
