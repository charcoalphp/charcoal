<?php
/**
 * Charcoal Object
 *
 * @package    Charcoal
 * @subpackage core
 *
 * @copyright (c) Locomotive 2007-2012
 * @author    Mathieu Ducharme <mat@locomotive.ca>
 * @version   2012-06-28
 * @since     Version 2012-03-01
 * @license   LGPL
 */

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Model\Model as Model;
use \Charcoal\Model\IndexableInterface as IndexableInterface;
use \Charcoal\Model\Source\Database as Database;

use \Charcoal\Loader\ObjectLoader as ObjectLoader;

class Object extends Model implements IndexableInterface
{
    const DEFAULT_KEY = 'id';

    private $_key;
    private $_id;
    private $_active = true;
    

    public function __construct($data=null)
    {
        // Use Model constructor...
        parent::__construct();

        if($data !== null) {
            $this->set_data($data);
        }

        // ... and add one option to set the primary key and this object table
        $metadata = $this->metadata();
        $key = isset($metadata['key']) ? $metadata['key'] : self::DEFAULT_KEY;
        $this->set_key($key);
    }

    public function set_data($data)
    {
        parent::set_data($data);

        if(isset($data['key'])) {
            $this->set_id($data['key']);
        }
        if(isset($data['id'])) {
            $this->set_id($data['id']);
        }
        if(isset($data['active'])) {
            $this->set_key($data['active']);
        }

        return $this;
    }

    public function set_key($key)
    {
        //pre($val);
        if(!is_scalar($key)) {
            throw new \InvalidArgumentException('Key argument must be scalar');
        }
        $this->_key = $key;

        return $this;
    }

    public function key()
    {
        return $this->_key;
    }
    
    public function set_id($val)
    {
        if(!is_scalar($val)) {
            throw new \InvalidArgumentException('Val argument must be scalar');
        }
        $this->{$this->_key} = $val;

        // Chainable
        return $this;
    }

    public function id()
    {
        $key = $this->key();
        if($key == 'id') {
            return $this->_id;
        }
        $func = [$this, $key];
        if(is_callable($func)) {
            return call_user_func($func);
        }
        else {
            throw new \Exception('Invalid key');
        }
    }

    public function set_active($active)
    {
        if(!is_bool($active)) {
            throw new \InvalidArgumentException('Active parameter needs to be bool');
        }
        $this->_active = $active;
        return $this;
    }

    public function active()
    {
        return $this->_active;
    }

    public function loader()
    {
        $metadata = $this->metadata();

        $source = new Database();
        $source->set_table('charcoal_admin_users');

        $loader = new ObjectLoader();
        $loader->set_obj($this);
        $loader->set_source($source);

        return $loader;
    }

    public function load($ident=null)
    {
        $loader = $this->loader();
        $data = $loader->load_data($ident);

    }
}
