<?php

namespace Charcoal\Validator;

// Local namespace dependencies
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

/**
* A full default implementation, as trait, of the ValidatableInterface.
*
* There is one additional abstract method: `create_validator()`
*/
trait ValidatableTrait
{
    /**
    * In-objet copy of the `ValidatorInterface` validator object
    * @var ValidatorInterface $validator
    */
    protected $validator;

    /**
    * @param ValidatorInterface $validator
    * @return ValidatableInterface Chainable
    */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
    * @return ValidatorInterface
    */
    public function validator()
    {
        if ($this->validator === null) {
            $this->validator = $this->createValidator();
        }
        return $this->validator;
    }

    /**
    * @return ValidatorInterface
    */
    abstract protected function createValidator();

    /**
    * @param ValidatorInterface $v
    * @return boolean
    */
    public function validate(ValidatorInterface &$v = null)
    {
        if ($v !== null) {
            $this->setValidator($v);
        }

        return $this->validator()->validate();
    }
}
