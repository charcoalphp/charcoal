<?php

/**
 * Charcoal Model class file
 * Part of the `charcoal-core` package.
 *
 * @author Mathieu Ducharme <mat@locomotive.ca>
 */

namespace Charcoal\Model;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * Charcoal Model class
 *
 * # The Charcoal Model
 * <figure>
 *   <img src="http:// charcoal.locomotive.ca/doc/assets/images/uml/charcoal.model.svg" alt="The Charcoal Model UML" />
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
 * The Model Attributes are stored in {@see \Charcoal\Property\PropertyInterface} objects. The properties are defined
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
}
