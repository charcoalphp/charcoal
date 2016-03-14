<?php

namespace Charcoal\Validator;

// Local namespace dependencies
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

/**
* Validatable Interface
*
* Add a validator to an object, as well as a `validate()` method.
*/
interface ValidatableInterface
{
    /**
    * @param ValidatorInterface $validator The validator object to use for validation.
    * @return ValidatableInterface Chainable
    */
    public function setValidator(ValidatorInterface $validator);

    /**
    * @return ValidatorInterface
    */
    public function validator();

    /**
    * @param ValidatorInterface $v Optional. A custom validator object to use for validation. If null, use object's.
    * @return boolean
    */
    public function validate(ValidatorInterface &$v = null);
}
