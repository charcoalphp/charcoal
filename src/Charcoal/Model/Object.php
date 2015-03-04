<?php
/**
 * Charcoal Object
 *
 * @package Charcoal
 * @subpackage core
 *
 * @copyright (c) Locomotive 2007-2012
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @version 2012-06-28
 * @since Version 2012-03-01
 * @license LGPL
 */

namespace Charcoal\Model;
use Charcoal;

/**
 * Charcoal Object class
 *
 * Main logic for all objects: this class extends the basic Charcoal Model (Charcoal_Base) to add a data mapper
 * (in the future: a Charcoal_Source) that acts as a sort of Active Record to the storage.
 * Charcoal Objects are specific Domain Objects that should be used through
 *
 * **Developer warning: Everything in this file must be triple-checked: this is core stuff.**
 *
 * # Methods
 * - id() and set_id()
 * - key() and set_key()
 * - table() and table_exists() and create_table()
 * - load() and load_key() / load_from() and load_random() and load_from_query
 * - from_flat_data() and from_sql_data()
 * - check_config()
 *
 * # Configuration data
 * The configuration system of Charcoal_Object is exactly the one from its parent class, <code>Charcoal_Base</code>.
 * The JSON object configuration file is therefore loaded by the same rules ({module}/config/{module}.{object-name}.json) and shares the
 * same structure (read the doc on Charcoal_Base and Charcoal_Config for more details.)
 *
 * Some configuration specificities:
 * - "config_type", for Charcoal_Object should always be "object" (unless more specific).
 * - "config_version" is unused as of 2014-05-12; always use 1 as its value.
 * - "properties" is where is the list of all properties, as ident=>config pair of values define all the object's properties. (Inherited from Charcoal_Base)
 * - "source" should be defined if the object is to be loaded from a database table
 *
 * # Inheritance
 * PHP Inheritcance should be quite straightforward, however, Charcoal Inheritance can be seen as a bit different with its
 * complex configuration system (most likely in JSON). Because most of the properties of a Charcoal_Object can be dynamic
 * (stored in the "properties" config key), it is possible to define a complex object without the use of a PHP class at all.
 * Loading a complex object from configuration without a PHP class is simply:
 * # Storing a configuration data in either {module}/config/{module}.{object-name}.json or in database config
 * # Instancing a Charcoal_Object as {object-name} type with <code>Charcoal_obj('object-name');
 * ## The configuration will be loaded dynamically and therefore the properties
 * # If a storage (such as a database table, most likely) is defined in the config's sources, then it will be automativally be created, its strcutre depending on its (dynamic) properties.
 * Because it is possible to load all the object data from the configuration, and because this configuration can be loaded dynamically,
 * it is quite possible to see a Charcoal-based system where all objects are "simply" created on-the-fly by a user interface.
 *
 * ## PHP Inheritance
 * However, a full PHP implementation of the children classes should always be preferred because it:
 * - Allows setting custom values before saving / updating
 * - Defines custom render pattern
 * - Defines custom validation rules
 * - Provides custom helper functions
 * - Makes documentation more complete
 * - etc.
 *
 * # Instancing an object
 * Instancing an object should never be done directly with the <code class="php">new</code> keyword but rather
 * *always* with <code class="php">Charcoal:obj($obj_type)</code> or <code class="php">Charcoal_Object::get($obj_type)</code>
 * ``php
 * // Instancing an object of "obj_type"
 * $my_obj = \Charcoal::obj('obj_type');
 *
 * // Loading the object that has the key "1"
 * $my_obj->load(1);
 * ```
 *
 * # Database source
 * The configuration of the database source (a table) is usually done in the config in conig[sources][default][table].
 * A typical configuration of the source looks like this:
 * ``` json
 * {
 * 	"sources":{
 *		"default":{
 *			"table":"TABLE_NAME",
 *			"table_type":"myisam"
 *		}
 * 	}
 * }
 * ```
 * The table type (storage engine) can be either "myisam" (default) or "innodb"
 * The table encoding is hardcoded to UTF-8.
 * The table "comment" is always the object name, as defined in the JSON
 * The default table order is still TODO.
 *
 * ## Database source methods
 * - table() gets the default table name
 * - table_exists() returns true if the table exists in the mysql db, false if not
 * - create_table() creates the database table from the object's config and properties
 * - update_table() ensures the database table is sync'ed with the latest object's configuration. (It doesn't delete anything but may add new columns to the structure)
 *
 * # Database operations
 * The most important feature Charcoal_Object adds on top of Charcoal_Base is its database (storage) support.
 * ## Loading an object
 * Loading an object by its ID (its key value) is as simple as:
 * ```php
 * // Load an object from storage (database, most likely) with ID 1
 * $obj = \Charcoal::obj('obj_type')->load(1);
 * ```
 * Loading from a different property value (should be unique to avoid conflict) is done with load_from
 * ```php
 * // Load an object of type "obj_type" from storage where "key" is "val"
 * $obj = Charcoal('obj_type')->load_from('key', 'val');
 * ```
 * ## Saving an object
 * ```php
 * // Set the data from a $_POST and then save it as a new object in storage (database)
 * $obj = \Charcoal::obj('obj_type');
 * $obj->from_flat_data($_POST);
 * $validation = $obj->validate(); // Usually, this would be used...
 * // By calling save(), pre_save() and post_save() will be called
 * $obj->save();
 * ```
 * ## Updating an object
 * ```php
 * // Set only one property and update
 * $obj->some_property = 'some_new_value';
 * $validation = $obj->validate(); // Usually, this would be used...
 * // By calling update(), pre_update() and post_update() will be called
 * $obj->update(array('some_property'));
 * ```
 * ## Deleting an object
 * ```php
 * // Removes from storage. Logging should be done in pre_delete() or post_delete()
 * $obj->delete();
 * ```
 * # Rendering and Patterns
 * Read the documentation on Rendering and Patterns from Charcoal_Base for information on the core concepts of patterns for Charcoal_Object's, as that's where its inherited from.
 * In addition to to
 *
 * @category Charcoal
 * @package Charcoal.Core
 * @subpackage Objects
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2014 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2014-03-20
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
 */
class Object extends Model
{
	const DEFAULT_KEY = 'id';

	/**
	 * Auto-increment integer ID
	 *
	 * ## Getter and setter notes
	 * The id() method does not always return the  exact id property, as it depends on the object's key.
	 * (Determined with key()). To get the raw $id value, use id_raw() method. Similarly, the set_id() method
	 * does not always affecct this property as it used the object's key too. To set the raw $id value,
	 * use set_id_raw() method.
	 * @var integer $id
	 * @see Property_Id
	 */
	public $id;

	/**
	 * Must be true to be displayed
	 * @var boolean $active
	 * @see Property_Boolean
	 */
	public $active;

	/**
	 * @var string $_key
	 * @access private
	 */
	private $_key;

	/**
	 * Charcoal_Object constructor takes care of setting up object's config
	 * @param string $config_name
	 * @todo 2012-06-28: DOC!
	 */
	public function __construct($config_name=null)
	{
		// Use Model constructor...
		parent::__construct($config_name);

		// ... and add one option to set the primary key and this object table
		$config = $this->metadata();
		$key = isset($config['key']) ? $config['key'] : self::DEFAULT_KEY;
		$this->set_key($key);
	}

	/**
	 * @todo 2012-06-28: DOC!
	 */
	final static public function get($obj_type='', $options=null)
	{
		$cfg = \Charcoal::$config;

		// Load overridden object type
		$orig = strtolower($obj_type);
		if(isset($cfg['objects'][$orig])) {
			if(isset($cfg['objects'][$orig]['replaced_by'])) {
				$obj_type = $cfg['objects'][$orig]['replaced_by'];
			}
		}

		$ns_obj_type = '\\'.str_replace('_', '\\', $obj_type);
		if($obj_type && class_exists($obj_type)) {
			return new $obj_type($options);
		}
		else if($obj_type && class_exists($ns_obj_type)) {
			return new $ns_obj_type($options);
		}
		else {
			return new \Charcoal\Object($obj_type, $options);
		}
	}

	/**
	 * Get the value of key key. Standardize on id()
	 *
	 * @return mixed
	 */
	public function id()
	{
		if(isset($this->_key) && isset($this->{$this->_key})) {
			return $this->{$this->_key};
		}
		else {
			// @todo 2012-09-08 Log error
			return null;
		}
	}

	/**
	 * Set the key value
	 *
	 * @param mixed $val
	 *
	 * @throws \InvalidArgumentException if the provided id is not a scalar
	 * @return Object (chainable)
	 */
	public function set_id($val)
	{
		if(!is_scalar($val)) {
			throw new \InvalidArgumentException('Val argument must be scalar');
		}
		$this->{$this->_key} = $val;

		// Chainable
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function active()
	{
		if($this->active === null) {
			// Objects are active by default
			return true;
		}

		return !!$this->active;
	}

	public function set_active($active=true)
	{
		$this->active = !!$active;

		return $this;
	}
	
	/**
	 * Get the surrogate key column / member name
	 *
	 * @return string
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Set the surrogate key name
	 *
	 * @param string $key
	 *
	 * @throws \InvalidArgumentException if the provided key is not a scalar
	 * @return Charcoal_Object (chainable)
	 */
	public function set_key($key)
	{
		//pre($val);
		if(!is_scalar($key)) {
			throw new \InvalidArgumentException('Key argument must be scalar');
		}
		$this->_key = $key;

		return $this;
	}

}
