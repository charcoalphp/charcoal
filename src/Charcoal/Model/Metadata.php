<?php
/**
 * Charcoal config class file
 *
 * @category Charcoal
 * @package Charcoal.Core
 * @subpackage Utilities
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2014-07-24
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
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
 * 
 * ## Configuration Loader
 *
 *
 * @category Charcoal
 * @package Charcoal.Core
 * @subpackage Utilities
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2012-07-24
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
 */
class Metadata implements \ArrayAccess
{
	/**
	 * Can be object, property, template, email etc.
	 * @var string $config_type
	 */
	public $config_type;
	/**
	 * Versioning will allow future backward-compatibility to be kept.
	 * As of 2015-02-20 this is not yet used.
	 * @var mixed $config_version
	 */
	public $config_version;

	/**
	 * @var string $ident
	 */
	public $ident;

	/**
	 * The name of the configuration
	 * - l10n array (strings with langs as keys)
	 * @var array $name
	 */
	public $name;
	/**
	 * Description
	 * - l10n array (strings with langs as keys)
	 * @var array $description
	 */
	public $description;
	/**
	 * Longer description
	 * - l10n array (strings with langs as keys)
	 * @var array $long_description
	 */
	public $long_description;
	/**
	 * - l10n array (strings with langs as keys)
	 * @var array $notes
	 */
	public $notes;

	/**
	 * Holds the properties of this configuration object
	 * @var array $properties
	 */
	public $properties;

	/**
	* The actual config data
	* @var array $data
	*/
	public $data;

	public $actions;

	public $source;

	/**
	 * Static "cache" of all loaded config (hierarchy result)
	 *
	 * @access private
	 */
	private static $_config;

	/**
	 * Static "cache" of all loaded raw config file
	 *
	 * @access private
	 */
	private static $_config_sources;

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

		foreach($data as $k => $v) {
			$this->{$k} = $v;
		}

		return $this;
	}
}
