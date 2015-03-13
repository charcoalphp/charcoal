<?php
/**
* Core (base) property file
*
* This file should be auto-included thanks to __autoload(), defined in charcoal.php
*
* @category Charcoal
* @package Core
* @subpackage Properties
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2014 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @link http://charcoal.locomotive.ca
* @version 2014-08-21
* @since Version 2012-03-01
*
* Changelog:
* - 2012-08-25: Clean up the save() and update() functions
*/

namespace Charcoal\Model;

use \Charcoal\Property\View as PropertyView;
use \Charcoal\Property\ViewController as PropertyViewController;

/**
* Core (base) property class
*
* Properties are the building blocks to everything Charcoal.
*
* Objects define concepts. Such as "a store", "a user", "an article", "a comment", "an image gallery", etc.
* Properties define the objects. Such as "a store's postal code", "a user's email", "an article date", etc.
*
* Each properties is defined with an ident (for example: "postal_code"), a type (for exemple: "string") and
* attributes / options (for example: "max_length"). What options (attributes) are revelant to a property depend
* on its type (for example, a property of type string have attributes about the string validation, possible
* length etc. while a property of type image have options about the image size, ratio, path, etc.) Some options
* are global (defined in Charcoal_Property) and thus available for all types of properties.
*
* Properties are used throughout Charcoal projects to define particular objects in a certain way.
* It is used for the following operations:
* - Loading an object from the database
* - Loading an object from a request ($_POST, for example)
* - Validating an object
* - Saving or updating an object to the database
* - Displaying the object property HTML
* - Creating the object form (the property input)
*
* Most of the time, you don't need to use properties directly, as most of their useful functions are called
* automatically from their parent objects. Therefore, as a charcoal developer, your interaction with properties
* will mostly be through the objects JSON configuration files.
*
* # Properties
* Because Charcoal_Property itself extends Charcoal_Base, each property has their own (meta-)property.
* Those properties are available in ALL types of properties (although some properties might not be used in some types)
* ```
* Property             | Type          | Default value           | Description
* -------------------- | ------------- | -------------           | -----------
* **ident**            | string        |                         | The identifier (name) of this property
* **type**             | string        |                         | The type of property (string, boolean, color, email, html, etc.)
*
* **input_type**       | string choice | Depends on type         | Determines how $this->input() will behave
* **display_type**     | string choice | Depends on type         | Determines how $this->display() will behave
* **text_type**        | string choice | Depends on type         | Determines how $this->text() will behave
*
* **input_options**    | array         | Depends on input_type   | The options (config) for all input types
* **display_options**  | array         | Depends on display_type | The options (config) for all input types
* **text_options**     | array         | Depends on input_type   | The options (config) for all input types
*
* **l10n**             | boolean       | false                   | If true, the object is translatable and its value is an array of l10n'ed values. Stored as multiple columns in the database.
* **hidden**           | boolean       | false                   | Advanced option. Hide from form and list (but not disabled)
* **multiple**         | boolean       | false                   | Multiple properties can hold more than one value.
* **multiple_options** | array         | Read documentation      |
*
* **required**         | boolean       | false                   | If true, this property *must* have a value. (Validation will fail if empty/zero)
* **read_only**        | boolean       | false                   | Read-only properties can never be edited
* **unique**           | boolean       | false                   | Unique properties should not share the same value across 2 objects
* **active**           | boolean       | true                    | Inactive properties are hidden everywhere / unused
* ```
*
* @category Charcoal
* @package Charcoal.Core
* @subpackage Properties
*
* @author Mathieu Ducharme <mat@locomotive.ca>
* @copyright 2014 Locomotive
* @license LGPL <https://www.gnu.org/licenses/lgpl.html>
* @version 2014-08-21
* @link http://charcoal.locomotive.ca
* @since Version 2012-03-01
*/
class Property extends \Charcoal\Model\Model
{
	/**
	* @var mixed Actual value of the
	*/
	private $val;

	/**
	* @var 
	*/
	private $label;

	/**
	* If true, the object is translatable and its value is an array of l10n'ed values
	* @var boolean $l10n
	*/
	private $l10n;

	/**
	* Hidden properties should not be shown visually, but their data might be.
	* @var boolean $hidden;
	*/
	private $hidden;

	/**
	* Multiple properties can hold more than one value
	* @var boolean $multiple
	*/
	private $multiple;

	/**
	* Array of options for multiple properties
	* - `separator` (default=",") How the values will be separated in the storage (sql)
	* - `min` (default=null) The minimum number of values. If null, <0 or NaN, then this is not taken into consideration
	* - `max` (default=null) The maximum number of values. If null, <0 or NaN, then there is not limit
	* @var mixed $multiple_options
	*/
	private $multiple_options;

	/**
	* If true, this property *must* have a value
	* @var boolean $required
	* @see Property_Boolean
	*/
	private $required;

	/**
	* Unique properties should not share he same value across 2 objects
	* @var boolean $unique
	*/
	private $unique;

	/**
	* Inactive properties should be hidden everywhere / unused
	* @var boolean $active
	*/
	private $active;


	/**
	*
	*/
	public function __construct($metadata_name=null)
	{
		// Set default values
		$this->set_l10n(false);
		$this->set_hidden(false);
		$this->set_multiple(false);
		$this->set_required(false);
		$this->set_unique(false);
		$this->set_active(true);

		// Model Constructor
		parent::__construct($metadata_name);


	}

	/**
	*
	*/
	public function __toString()
	{
		$val = $this->val();
		if(is_string($val)) {
			return $val;
		}
		else {
			return '';
		}
	}

	/**
	* This should be the main (and only) way to create new Property_* object
	*
	* @param string
	* @param array
	*
	* @return \Charcoal\Property
	*/
	final static public function get($type='')
	{
		$class_name = '\Charcoal\Property\\'.str_replace('_', '\\', $type);
		if(class_exists($class_name)) {
			return new $class_name();
		}
		else {
			return new \Charcoal\Model\Property();
		}

	}

	/**
	* @param array $data
	* @throws \InvalidArgumentException if the data parameter is not an array
	* @return Property Chainable
	*/
	public function set_data($data)
	{
		if(!is_array($data)) {
			throw new \InvalidArgumentException('Data must be an array');
		}

		//parent::set_data($data);

		if(isset($data['val'])) {
			$this->set_val($data['val']);
		}
		if(isset($data['label'])) {
			$this->set_label($data['label']);
		}
		if(isset($data['l10n'])) {
			$this->set_l10n($data['l10n']);
		}
		if(isset($data['hidden'])) {
			$this->set_hidden($data['hidden']);
		}
		if(isset($data['multiple'])) {
			$this->set_multiple($data['multiple']);
		}
		if(isset($data['multiple_options'])) {
			$this->set_multiple_options($data['multiple_options']);
		}
		if(isset($data['required'])) {
			$this->set_required($data['required']);
		}
		if(isset($data['unique'])) {
			$this->set_unique($data['unique']);
		}
		if(isset($data['active'])) {
			$this->set_active($data['active']);
		}

		return $this;
	}

	/**
	* @param mixed
	* @return Property (Chainable)
	*/
	public function set_val($val)
	{
		$this->val = $val;

		return $this;
	}

	/**
	* @return mixed
	*/
	public function val()
	{
		return $this->val;
	}

	/**
	* @param mixed $label
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_label($label)
	{

		$this->label = $label;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function label()
	{
		return $this->label;
	}

	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_l10n($l10n)
	{
		if(!is_bool($l10n)) {
			throw new \InvalidArgumentException('l10n must be a boolean');
		}
		$this->l10n = $l10n;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function l10n()
	{
		return !!$this->l10n;
	}

	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_hidden($hidden)
	{
		if(!is_bool($hidden)) {
			throw new \InvalidArgumentException('hidden must be a boolean');
		}
		$this->hidden = $hidden;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function hidden()
	{
		return !!$this->hidden;
	}

	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_multiple($multiple)
	{
		if(!is_bool($multiple)) {
			throw new \InvalidArgumentException('multiple must be a boolean');
		}
		$this->multiple = $multiple;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function multiple()
	{
		return !!$this->multiple;
	}

	/**
	* @param array
	* @throws \InvalidArgumentException if the paramter is not an array
	* @return Property (Chainable)
	*/
	public function set_multiple_options($multiple_options)
	{
		if(!is_array($multiple_options)) {
			throw new \InvalidArgumentException('multiple options must be an array');
		}
		$default_options = [
			'separator'	=> ',',
			'min'		=> 0,
			'max'		=> 0
		];
		$options = array_merge($default_options, $multiple_options);
		$this->multiple_options = $options;
		return $this;
	}

	/**
	* @return array
	*/
	public function multiple_options()
	{
		return $this->multiple_options;
	}
	
	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_required($required)
	{
		if(!is_bool($required)) {
			throw new \InvalidArgumentException('required must be a boolean');
		}
		$this->required = $required;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function required()
	{
		return !!$this->required;
	}

	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_unique($unique)
	{
		if(!is_bool($unique)) {
			throw new \InvalidArgumentException('unique must be a boolean');
		}
		$this->unique = $unique;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function unique()
	{
		return !!$this->unique;
	}

	/**
	* @param boolean
	* @throws \InvalidArgumentException if the paramter is not a boolean
	* @return Property (Chainable)
	*/
	public function set_active($active)
	{
		if(!is_bool($active)) {
			throw new \InvalidArgumentException('active must be a boolean');
		}
		$this->active = $active;
		return $this;
	}

	/**
	* @return boolean
	*/
	public function active()
	{
		return !!$this->active;
	}

	public function validate_required()
	{

	}

	public function validate_unique()
	{

	}

	/**
	* @param string
	* @return string
	*/
	public function render($template)
	{
		$view = new PropertyView();
		$controller = PropertyViewController::get($this);
		return $view->render($template, $controller);
	}

	/**
	*
	*/
	public function render_template($template_ident, $template_options=null)
	{
		$view = new PropertyView();
		$controller_name = '\Charcoal\Property\ViewController\\'.str_replace(['.', '_', '\\'], '', $template_ident);
		if(class_exists($controller_name)) {
			$controller = new $controller_name($this);
		}
		else {
			$controller = new PropertyViewController($this);
		}
		
		if(is_array($template_options)) {
			$controller->set_data($template_options);
		}

		return $view->render_template($template_ident, $controller);
	}
}
