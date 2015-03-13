<?php
/**
 * Charcoal base class file
 *
 * @category Charcoal
 * @package Charcoal
 * @subpackage Model
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 * @copyright 2012-2015 Locomotive
 * @license LGPL <https://www.gnu.org/licenses/lgpl.html>
 * @version 2015-02-25
 * @link http://charcoal.locomotive.ca
 * @since Version 2012-03-01
 */

namespace Charcoal\Model;

use \Charcoal\Charcoal as Charcoal;
use \Charcoal\Loader\MetadataLoader as MetadataLoader;
use \Charcoal\Model\Metadata as Metadata;
use \Charcoal\Model\Property as Property;

/**
* Charcoal base model
*
* The base for everything that is a Charcoal "Domain Object Model"
* > "An object model of the domain that incorporates both behavior and data."
*
* In MVC terms, `\Charcoal\Base` is one of the 3 core classes in the Model Layer
* - `\Charcoal\Model\Model` for Domain Objects
* - *Charcoal_Source* for Data Mappers
* - *Charcoal_Service* for Services
*
* # The Charcoal Model
* <figure>
*   <img src="http://charcoal.locomotive.ca/doc/assets/images/uml/charcoal.model.svg" alt="The Charcoal Model UML" />
*   <figcaption>The Charcoal Model Class Diagram</figcaption>
* </figure>
*
* # Custom Object Type
* It is possible to attach a custom object type (`$_obj_type`) to a Model. This will allow the
* various loaders (metadata and source data)
*
* # Configuration / Metadata
* In Charcoal, all models are held in an instance of `\Charcoal\Model\Model` 
* and its configuration meta-datastructure is defined in a `\Charcoal\Model\Metadata` object.
*
* To access the metadata, use `$this->metadata()`. To set metadata, use either
*
* # Properties
* The Model Attributes are stored in `\Charcoal\Model\Property` objects. 
*
* # Data Source
* The Model data (which is stored internally in the class) can be stored 
*
* # Data validation
* Once an object has had its data filled (from a form, database, or other source), it is possible to check
* wether the data is conform to the object definition, as defined by it's properties and meta-properties.
* This check is done with the `validate()` function.
*
* @category Charcoal
* @package Charcoal
* @subpackage Model
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2012-2014 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @version 2014-10-04
* @link http://charcoal.locomotive.ca
*
* @see \Charcoal\Model\Metadata
* @see \Charcoal\Model\Property
*
* @since Version 2012-03-01
*/
class Model
{
	//use \\Charcoal\\Trait\\Renderer;

	/**
	 * Always store the object type in this var
	 *
	 * The obj type can be different from the class name, in case of objects
	 * that are defined 100% through the JSON metadata file(s).
	 *
	 * @var string $_obj_type
	 * @see self::obj_type()
	 */
	private $_obj_type;

	/**
	 * Always store the array of properties (in a static var) for all object types
	 *
	 * @var array $_properties
	 * @see self::property()
	 */
	private $_properties = null;

	/**
	 * Store (in a static var) the class metadata
	 *
	 * Until the code is ported to late-static-bindings, it is an
	 * array that is share amongst all class (with the object type as key)
	 *
	 * @var \Charcoal\Model\Config $_metadata
	 * @see self::metadata()
	 * @see self::set_metadata()
	 */
	static protected $_metadata = null;

	/**
	 * Constructor for the base objects (models)
	 *
	 * Takes care of loading the proper metadatauration and setting its data, if any.
	 *
	 * @param string $metadata_name The name of the metadatauration file to load. If empty
	 * @param array $extra_metadata @todo Merge this extra metadatauration option to
	 *
	 * @see self::load_metadata()
	 * @see self::set_data()
	 */
	public function __construct($metadata_name=null)
	{
		// Allow namespace. But the metadata_name is normalized to underscores (Should it also be lowercased?)
		$metadata_name = ($metadata_name !== null) ? $metadata_name : str_replace('\\', '_', get_class($this));
		$this->_obj_type = $metadata_name;		
	}

	/**
	* Sets the metadata of the object model.
	* 
	* This can be either done
	*
	* @todo 2012-06-28: DOC!
	*
	* @param array|\Charcoal\Model\Config $metadata Can be null (skip this step) array or \Charcoal\Model\Config object
	*
	* @throws \InvalidArgumentException if metadata argument is not an array / Metadata object
	* @return \Charcoal\Model\Base (Chainable)
	*/
	public function set_metadata($metadata)
	{
	
		if(is_array($metadata)) {
			$c = new Metadata();
			$c->set_data($metadata);
			static::$_metadata = $c;
		}
		else if($metadata instanceof Metadata)
		{
			static::$_metadata = $metadata;
		}
		else {
			throw new \InvalidArgumentException('Metadata argument is invalid (must be array or Model\\Medatadata object)');
		}

		// If the metadata contains "data", then automatically set the initial data to the value
		if(isset(static::$_metadata['data'])) {
			$this->set_data($metadata['data']);
		}

		// Chainable
		return $this;
	}

	/**
	 * Get the object's metadatauration object
	 *
	 * The object metadatauration should be stored as a \Charcoal\Model\Metadata object.
	 * \Charcoal\Model\Config implements ArrayAccess so it can be used like an array too.
	 *
	 * @return \Charcoal\Model\Metadata
	 *
	 * @todo With PHP 5.3+ we could use the static keywords for Late-Static-Binding instead of an array
	 */
	public function metadata()
	{
		//pre($this->obj_type());
		if(!isset(static::$_metadata)) {
			// @todo Log error, default metadata loaded
			return $this->load_metadata($this->obj_type());
		}

		return static::$_metadata;
	}



	/**
	 * Get the object type of the actual object
	 *
	 * In most cases, the "obj_type" is the name of the PHP class of the object.
	 * It is possible, however, to have empty objects in Charcoal that are 100% defined with a JSON metadata.
	 * (In this case, the obj_type would be `\Charcoal\Model\Base` or `Charcoal_Object`)
	 *
	 * @todo 2012-06-28: DOC!
	 * @return string The object type (class name or metadata name)
	 */
	public function obj_type()
	{
		if($this->_obj_type) {
			return $this->_obj_type;
		}
		else {
			return str_replace('\\', '_', get_class($this));
		}
	}

	/**
	 * Load a metadata file and store it as a static var
	 *
	 * @param string $metadata_name
	 * @param array $hierarchy
	 *
	 * @return \Charcoal\Model\Config
	 *
	 * @todo 2012-06-28: DOC!
	 * @todo 2014-04-01: Use late static binding instead of an array
	 */
	public function load_metadata($metadata_name)
	{
		//pre($metadata_name);
		if(!isset(static::$_metadata) || (static::$_metadata === null)) {
			$metadata_loader = new MetadataLoader();
			$metadata = $metadata_loader->load($metadata_name);
			$this->set_metadata($metadata);
			
		}
		return $metadata;
	}

	/**
	 * Sets the data
	 *
	 * This function takes an array and fill the object with its value.
	 *
	 * @param array $data
	 * @throws \InvalidArgumentException if the data parameter is not an array
	 * @return \Charcoal\Model\Base Returns self (Chainable)
	 */
	public function set_data($data)
	{
		if(!is_array($data)) {
			throw new \InvalidArgumentException(__CLASS__.'::'.__FUNCTION__.'() - Data must be an array');
		}

		foreach($data as $prop => $val) {
			$this->{$prop} = $val;
		}

		// Chainable
		return $this;
	}

	/**
	 * Sets the data
	 *
	 * This function takes an array and fill the object with its value.
	 *
	 * @param array $data
	 * @return \Charcoal\Model\Base Returns self (Chainable)
	 */
	public function set_flat_data($data)
	{
		if(!is_array($data)) {
			// @todo Log Error
			return $this;
		}

		foreach($data as $prop => $val) {
			$this->{$prop} = $val;
		}

		// Chainable
		return $this;
	}

	/**
	 * Return the object data as an array
	 *
	 * @return mixed
	 * @todo 2012-06-28: DOC!
	 */
	public function data()
	{
		// Return value is array
		$data = [];

		$metadata = $this->metadata();
		$props = $metadata['properties'];

		if(!is_array($props)) {
			// Error. Invalid object? @todo error report
			// @todo Throw exception here?
			return false;
		}

		foreach($props as $property_ident => $property_options) {
			$p = $this->p($property_ident);

			if (!$p instanceof \Charcoal\Model\Property) {
				continue;
			}
			$data[$property_ident] = $this->p($property_ident)->val();
		}

		return $data;
	}

	/**
	 * Validate the object according to its properties' rules
	 *
	 * @param array $props
	 *
	 * @return array
	 * @todo Return a Charcoal_Validation object
	 *
	 * @see \Charcoal\Model\Property::validate()
	 *
	 */
	public function validate($props=null)
	{
		$validations = [];
		$response    = [];

		if($props === null) {
			$props = $this->metadata()->properties;
		}

		foreach($props as $property_ident => $property_options) {

			$p = $this->p( $property_ident );

			if(!$p || (isset($p->active) && $p->active === false)) {
				// This property is invalid or deactivated. Do not check it.
				continue;
			}

			// VALIDATE PROPERTY
			$validations[$property_ident] = $p->validate();

			// INVALID PROPERTIES
			if($validations[$property_ident]['success'] === false) {
				// ARRAY SOURCE
				if(is_array($validations[$property_ident]['messages']) && $validations[$property_ident]['messages']) {
					foreach($validations[$property_ident]['messages'] as $m) {
						if($m) {
							$response[$property_ident][] = $m;
						}
					}
				}
				// STRING SOURCE
				else if(is_string($validations[$property_ident]['messages'] ) ) {
					$response[$property_ident][] = $validations[$property_ident]['messages'];
				}
			}
		}

		return $response;
	}

	/**
	 * Return an array of \Charcoal\Model\Property
	 *
	 * @param boolean
	 *
	 * @return array
	 */
	public function properties()
	{
		$properties = [];
		$metadata = $this->metadata();

		if(!isset($metadata['properties']) || empty($metadata['properties'])) {
			return [];
		}

		foreach($metadata['properties'] as $property_ident => $opts) {
			// Get the property object of this definition
			$properties[$property_ident] = $this->property($property_ident);
		}

		return $properties;
	}

	/**
	 * Get an object's property
	 *
	 * @param string $property_ident	The property ident to return
	 *
	 * @throws \InvalidArgumentException if the property_ident is not a scalar
	 * @throws \Exception if the requested property is invalid
	 * @return \Charcoal\Model\Property The \Charcoal\Model\Property if found, null otherwise
	 *
	 * @see \Charcoal\Model\Base::p()
	 */
	public function property($property_ident)
	{
		if(!is_scalar($property_ident)) {
			throw new \InvalidArgumentException('Invalid ident argument (must be scalar)');
		}

		$metadata = $this->metadata();

		if(!isset($metadata['properties']) || empty($metadata['properties'])) {
			throw new \Exception('Invalid model metadata - No properties defined');
		}

		$properties_metadata = $metadata['properties'];
		if(!isset($properties_metadata[$property_ident])) {
			throw new \Exception(sprintf('Invalid property: %s (not defined in metadata)', $property_ident));
		}

		$property_metadata = $properties_metadata[$property_ident];
		if(!isset($property_metadata['type'])) {
			throw new \Exception(sprintf('Invalid property: %s (type is undefined)', $property_ident));
		}

		$property_type = $property_metadata['type'];
		$property = Property::get($property_type);
		$property->set_data($property_metadata);

		$property_value = isset($this->{$property_ident}) ? $this->{$property_ident} : null;
		if($property_value !== null) {
			$property->set_val($property_value);
		}

		return $property;
	}

	/**
	 * Shortcut (alias) for: 
	 * - `property()` if the first parameter is set, _or_
	 * - `properties()` if the property_ident is not set (null)
	 *
	 * @param string $property_ident		The property ident to return
	 * @param array $property_options 	Extra options to set to the property
	 * @param boolean $force_reload		If true, property will be reloaded from metadata / options
	 *
	 * @return array|\Charcoal\Model\Property
	 *
	 * @see self::property()
	 * @see self::properties()
	 */
	public function p($property_ident=null)
	{
		if($property_ident === null) {
			return $this->properties();
		}
		// Alias for property()
		return $this->property($property_ident);
	}

	/**
	* @param string
	* @return string
	*/
	public function render($template)
	{
		$view = new View();
		$controller = ViewController::get($this);
		return $view->render($template, $controller);
	}

	/**
	*
	*/
	public function render_template($template_ident, $template_options=null)
	{
		$view = new View();
		
		$controller_name = '\Charcoal\Model\ViewController\\'.str_replace(['.', '_', '\\'], '', $template_ident);
		if(class_exists($controller_name)) {
			$controller = new $controller_name($this);
		}
		else {
			$controller = new ViewController($this);
		}
		if(!empty($template_options)) {
			$controller->set_data($template_options);
		}

		return $view->render_template($template_ident, $controller);
	}

}
