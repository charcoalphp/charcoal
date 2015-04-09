<?php
/**
 * Charcoal Model class file
 * Part of the `charcoal-core` package.
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 */

namespace Charcoal\Model;


use \Charcoal\Charcoal as Charcoal;

use \Charcoal\Metadata\MetadataLoader as MetadataLoader;

use \Charcoal\Model\AbstractModel as AbstractModel;
use \Charcoal\Model\ModelMetadata as Metadata;
use \Charcoal\Model\Property as Property;

use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

use \Charcoal\View\ViewInterface as ViewInterface;
use \Charcoal\Model\ModelView as ModelView;

/**
* Charcoal Model class
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
* # Metadata
* In Charcoal, all models are held in an instance of `\Charcoal\Model\Model`
* and its configuration meta-data structure is defined in a `\Charcoal\Model\Metadata` object.
*
* ## Loading metadata
* To access the metadata, use `$this->metadata()`. To set metadata, use either
*
* # Properties
* The Model Attributes are stored in `\Charcoal\Model\Property` objects. The properties are defined
* in the Model's `metadata` and can be accessed either with `p($ident)` to retrieve a property or with
* `properties()` to get all properties.
*
* # Data Source
* The Model data (which is stored internally in the class) can be stored in a storage `Source` object.
* There is only one source type currently implemented: `\Charcoal\Source\Database`.
*
* ## Loading from source
* ...
*
* ## Loading into Collection
* ...
*
* # Data validation
* Once an object has had its data filled (from a form, database, or other source), it is possible to check
* wether the data is conform to the object definition, as defined by it's properties and meta-properties.
* This check is done with the `validate()` function.
*
* The `validate()` method always return a boolean (`true` for success and `false` if there was any
* validation error(s)). The validation details are held in a `Validator` object which can then be
* accessed with the `validator()` method.
*
* # Rendering a model
* Every Charcoal Model can be rendered with the help of a `View` and a `ViewController`.
* Or, more precisely, a `\Charcoal\View\ModelView` and a `\Charcoal\View\ModelViewController`.
*/
class Model extends AbstractModel
{
    //use \\Charcoal\\Trait\\Renderer;

    /**
    * @var array $_properties
    */
    private $_properties;

    /**
    * @var Metadata $_metadata
    */
    private $_metadata;

    /**
    * @var string $_metadata_ident
    */
    private $_metadata_ident;

    /**
    * @var Validator $_validator
    */
    protected $_validator;

    /**
     * Constructor for the base objects (models)
     *
     * Takes care of loading the proper metadatauration and setting its data, if any.
     *
     * @param string $metadata_name  The name of the metadatauration file to load. If empty
     * @param array  $extra_metadata @todo Merge this extra metadatauration option to
     *
     * @see self::load_metadata()
     * @see self::set_data()
     */
    public function __construct($data=null)
    {
        if($data !== null) {
            $this->set_data($data);
        }
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
            $this->_metadata = $c;
        }
        else if($metadata instanceof Metadata) {
            $this->_metadata = $metadata;
        }
        else {
            throw new \InvalidArgumentException('Metadata argument is invalid (must be array or Medatadata object)');
        }

        // If the metadata contains "data", then automatically set the initial data to the value
        if(isset($this->_metadata['data'])) {
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
    */
    public function metadata()
    {
        if($this->_metadata === null) {
            // @todo Log error, default metadata loaded
            return $this->load_metadata();
        }

        return $this->_metadata;
    }

    public function set_metadata_ident($metadata_ident)
    {
        $this->_metadata_ident = $metadata_ident;
        return $this;
    }

    public function metadata_ident()
    {
        if($this->_metadata_ident === null) {
            $obj_type = get_class($this);
            return str_replace(['\\', '.'], '/', strtolower($obj_type));
        }
        else {
            return $this->_metadata_ident;
        }
    }

    /**
     * Load a metadata file and store it as a static var
     *
     * @param string $metadata_name
     * @param array  $hierarchy
     *
     * @return Metadata
     */
    public function load_metadata($metadata_name=null)
    {
        if($metadata_name === null) {
            $metadata_name = $this->metadata_ident();
        }
        $metadata_loader = new MetadataLoader();
        $metadata = $metadata_loader->load($metadata_name);
        $this->set_metadata($metadata);
        
        return $metadata;
    }

    public function source($source_ident=null)
    {
        $metadata = $this->metadata();
        //var_dump($metadata);
        if($source_ident === null) {
            // Default source ident
            $source_ident = isset($metadata['default_source']) ? $metadata['default_source'] : '';
        }
    }

    /**
    * Sets the data
    *
    * This function takes an array and fill the object with its value.
    *
    * @param  array $data
    * @throws \InvalidArgumentException if the data parameter is not an array
    * @return Model (Chainable)
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
     * @param  array $data
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
     * @todo   2012-06-28: DOC!
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

    public function set_validator(ValidatorInterface $v)
    {
        $this->_validator = $v;
    }

    public function validator()
    {
        if($this->_validator === null) {
            $this->_validator = new Validator($this);
        }
        return $this->_validator;
    }

    /**
     * Validate the Model data.
     *
     * @param Validator $v
     *
     * @return boolean
     */
    public function validate(ValidatorInterface &$v=null)
    {
        if($v === null) {
            $v = $this->validator();
        }

        //$v->validate();
        return true;
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
     * @param string $property_ident The property ident to return
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
        $property->set_ident($property_ident);
        $property->set_data($property_metadata);

        $property_value = isset($this->{$property_ident}) ? $this->{$property_ident} : null;
        if($property_value !== null) {
            $property->set_val($property_value);
        }

        return $property;
    }

    /**
     * Shortcut (alias) for:
     * - `property()` if the first parameter is set,
     * - `properties()` if the property_ident is not set (null)
     *
     * @param string  $property_ident   The property ident to return
     * @param array   $property_options Extra options to set to the property
     * @param boolean $force_reload     If true, property will be reloaded from metadata / options
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

    public function set_view(ViewInterface $view)
    {
        $this->_view = $view;
        return $this;
    }

    public function view()
    {
        if($this->_view === null) {
            $this->_view = new ModelView();
        }
        return $this->_view;
    }

    /**
    * Render the Model
    *
    * If the optional `template` parameter is defined, then it will render this
    * template using the current Model ($this) as context. If it is not set, then
    * it will try to load the appropriate template from the Model's type.
    *
    * @param  string $template
    * @return string The rendered template
    */
    public function render($template=null)
    {
        $view_data = [
            'template'=>$template,
            'context'=>$this
        ];
        $this->view()->set_data($view_data);
        return $this->view()->render();
    }

    /**
    * Render the Model using a template ident
    *
    * @param  string $template_ident
    * @return string The rendered template
    */
    public function render_template($template_ident)
    {
        $view_data = [
            'context'=>$this
        ];
        $view = new ModelView($view_data);
        return $view->set_context($this)->render_template($template_ident);
    }

}
