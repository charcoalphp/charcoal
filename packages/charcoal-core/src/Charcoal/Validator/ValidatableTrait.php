<?php

namespace Charcoal\Validator;

// From 'charcoal-core'
use Charcoal\Validator\ValidatorInterface;

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
     * @param ValidatorInterface $validator The validator object to use for validation.
     * @return self
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
     * @param ValidatorInterface $v Optional. A custom validator object to use for validation. If null, use object's.
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
