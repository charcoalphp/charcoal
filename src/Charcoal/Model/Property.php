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
* # Available methods
* The functions are grouped in a few concepts:
*
* ## Base functions (auto, getters, setters, etc.)
* - __toString()
* - get()
* - config()
* - set_config()
* - val()
* - set_val()
* - obj()
* - set_obj()
* - lang()
* - set_lang()
* - label()
* - icon()
*
* ## Display functions (text, display, input)
* - input_types()
* - display_types()
* - text_types()
* - input()
* - input_name()
* - input_id()
* - input_class()
* - display()
* - text()
* - render()
*
* ## Data & Validation functions
* - from_raw_data()
* - to_raw_data()
* - validate()
* - check_config()
*
* ## Database CRUD functions
* Database CRUD operations are usually hidden from the public API as they are called automatically through the Charcoal_Object's
* save(), update() and delete() functions. Charcoal_Object::save() and Charcoal_Object::update() always call the save() method
* of all their properties before saving to validate and sanitize the saved value. Charcoal::
*
* For an example of properties that need to rewrite their save() or delete() methods, look at `Property\File, `Property\Image or
* - **save()**
* - **delete()**
*
* ## SQL functions
* Although most interaction with the SQL driver are hidden from the public API, it is still possible to define how each
* property is stored in the database and retrive through filters. The following methods can be reimplemented in each
* property type if the default behavior is not desired:
* - sql_columns()
* - _sql_columns()
* - sql_type
* - sql_filter
* - sql_values
*
* # Core Property Types
* ```
* Type        | Class                  | Module   | Description
* boolean     | `Property\Boolean`     | core     | Simple true / false value
* callback    | `Property\Callback`    | core     | The value returned from a callback function (object method)
* choice      | `Property\Choice`      | core     | Choice within a list of options
* date        | `Property\Date`        | core     | Only the "date" portion of a "datetime" (no time)
* dateformat  | `Property\Dateformat`  | core     | Date format string (advanced)
* datetime    | `Property\Datetime`    | core     | Date and time value
* email       | `Property\Email`       | core     | Email address
* file        | `Property\File`        | core     | File (upload)
* float       | `Property\Float`       | core     | Float number
* html        | `Property\Html`        | core     | HTML text
* id          | `Property\Id`          | core     | Unique ID, Auto-incremental, UUID or uniq
* image       | `Property\Image`       | core     | Image file
* integer     | `Property\Integer`     | core     | Integer number
* ip          | `Property\Ip`          | core     | IP address
* json        | `Property\Json`        | core     | Complex data structure stored as JSON
* lang        | `Property\Lang`        | core     | Language choice withing the available languages
* month       | `Property\Month`       | core     | Month value
* number      | `Property\Number`      | core     | Number
* object      | `Property\Object`      | core     | Allows objects to be linked together
* object_type | `Property\Objtype`     | core     | An existing object type. (Object that has a Charcoal definition as PHP and/or JSON)
* password    | `Property\Password`    | core     | Encrypted (or not) password
* phone       | `Property\Phone`       | core     | Phone number
* string      | `Property\String`      | core     | Basic string of any type
* text        | `Property\Text`        | core     | Longer string (text)
* time        | `Property\Time`        | core     | Only the "time" portion of "datetime" (no date)
* video       | `Property\Video`       | core     | Video file
* widget      | `Property\Widget`      | core     | References an existing Charcoal Widget
* year        | `Property\Year`        | core     | Year
* youtube     | `Property\Youtube`     | core     | Youtube ID
* ```
* More property types are available in the various Charcoal modules. Read their respective documentation for more details.
*
* # Unit Tests
* Unit tests can be found
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
	private $_val;

	/**
	*
	*/
	public function __toString()
	{
		return $this->val();
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

	public function set_val($val)
	{
		$this->_val = $val;

		return $this;
	}

	public function val()
	{
		return $this->_val;
	}
}
