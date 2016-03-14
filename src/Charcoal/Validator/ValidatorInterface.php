<?php

namespace Charcoal\Validator;

/**
* A validator is attached to a model that implements ValidatableInterface and validate an object.
*/
interface ValidatorInterface
{
    /**
    * @param string $msg The error message.
    * @return ValidatorInterface Chainable
    */
    public function error($msg);

    /**
    * @param string $msg The warning message.
    * @return ValidatorInterface Chainable
    */
    public function warning($msg);
    /**
    * @param string $msg The notice message.
    * @return ValidatorInterface Chainable
    */
    public function notice($msg);

    /**
    * @param string $level The log level ('error', 'warning' or 'notice').
    * @param string $msg   The message.
    * @return ValidatorInterface Chainable
    */
    public function log($level, $msg);

    /**
    * @return array
    */
    public function results();

    /**
    * @return boolean
    */
    public function validate();
}
