<?php

namespace Charcoal\Validator;

use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

/**
* A full default implementation, as trait, of the ValidatableInterface.
*/
trait ValidatableTrait
{
    private $_validator;

    /**
    * @param ValidatorInterface
    * @return ValidatableInterface Chainable
    */
    public function set_validator(ValidatorInterface $validator)
    {
        $this->_validator = $validator;
        return $this;
    }

    /**
    * @return ValidatorInterface
    */
    public function validator()
    {
        return $this->_validator;
    }
    
    /**
    * @param ValidatorInterface
    * @return bool
    */
    public function validate(ValidatorInterface &$v=null)
    {
        if($v !== null) {
            $this->set_validator($v);
        }

        return $this->validator()->validate();
    }
}
