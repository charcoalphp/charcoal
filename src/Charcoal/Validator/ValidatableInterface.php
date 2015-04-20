<?php

namespace Charcoal\Validator;

use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

interface ValidatableInterface
{
    /**
    * @param ValidatorInterface
    * @return ValidatableInterface Chainable
    */
    public function set_validator(ValidatorInterface $validator);

    /**
    * @return ValidatorInterface
    */
    public function validator();
    
    /**
    * @param ValidatorInterface
    * @return bool
    */
    public function validate(ValidatorInterface &$v = null);
}
