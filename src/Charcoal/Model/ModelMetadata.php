<?php
/**
 * Charcoal config class file
 *
 * @category   Charcoal
 * @package    Charcoal.Core
 * @subpackage Utilities
 *
 * @author    Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license   LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version   2014-07-24
 * @link      http://charcoal.locomotive.ca
 * @since     Version 2012-03-01
 */

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Helper\Cache as Cache;

/**
 * Charcoal config class
 *
 * The `Charcoal\Model\Config` object implements ArrayAccess so it's properties can be
 * accessed just like an array().
 *
 * This class holds a Model configuration, which represents thewhich typically comes from JSON files or the DB config.
 *
 * It holds both the main `Charcoal::$config` global configuration and all the Charcoal_Base
 * (Charcoal_Object) object configuration.
 *
 * ## The global config
 *
 * ## Model config
 * ...
 *
 * ## Configuration Loader
 * ...
 *
 * @category   Charcoal
 * @package    Charcoal.Core
 * @subpackage Utilities
 *
 * @author    Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license   LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version   2012-07-24
 * @link      http://charcoal.locomotive.ca
 * @since     Version 2012-03-01
 */
class ModelMetadata implements \ArrayAccess
{

    /**
     * @var string $_ident
     */
    private $_ident;



    /**
     * Holds the properties of this configuration object
     * @var array $properties
     */
    private $_properties = [];

    /**
    * The actual config data
    * @var array $data
    */
    public $data;

    public function __construct($data=null)
    {
        if($data !== null) {
            $this->set_data($data);
        }
    }

    /**
     * ArrayAccess isset(config[a])
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * ArrayAccess config[a]
     */
    public function offsetGet($offset)
    {
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
    * ArrayAccess config[a] = '';
    * @throws \InvalidArgumentException if the offset is not set ($config[] = '')
    */
    public function offsetSet($offset, $value)
    {
        if(empty($offset)) {
            throw new \InvalidArgumentException('Offset is required');
        }
        $this->{$offset} = $value;
    }

    /**
     *  ArrayAcces unset(config[a])
     */
    public function offsetUnset($offset)
    {
        $this->{$offset} = null;
        unset($this->{$offset});
    }

    /**
    * @param array
    *
    * @throws \InvalidArgumentException if the data parameter is not an array
    * @return Metadata (Chainable)
    */
    public function set_data($data)
    {
        if(!is_array($data)) {
            throw new \InvalidArgumentException('Data parameter must be an array');
        }

        if(isset($data['properties'])) {
            $this->set_properties($data['properties']);
        }

        foreach($data as $k => $v) {
            $this->{$k} = $v;
        }

        return $this;
    }

    public function set_ident($ident)
    {
        if(!is_string($ident)) {
            throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'Ident must be a string');
        }
        $this->_ident = $ident;

        return $this;
    }

    public function ident()
    {
        return $this->_ident;
    }

    public function set_properties($properties)
    {
        if(!is_array($properties)) {
            throw new \InvalidArgumentException('Properties need to be an array');
        }
        $this->_properties = $properties;
        return $this;
    }

    public function properties()
    {
        return $this->_properties;
    }
}
