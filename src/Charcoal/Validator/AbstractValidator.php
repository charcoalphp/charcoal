<?php

namespace Charcoal\Validator;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Validator\ValidatorInterface;
use \Charcoal\Validator\ValidatableInterface;
use \Charcoal\Validator\ValidatorResult;

/**
* An abstract class that implements most of ValidatorInterface.
*
* The only abstract method in the class is `validate()`
*/
abstract class AbstractValidator implements ValidatorInterface
{
    const ERROR   = 'error';
    const WARNING = 'warning';
    const NOTICE  = 'notice';

    /**
    * @var ValidatableInterface
    */
    protected $model;

    /**
    * @var ValidatorResult[] $results
    */
    private $results = [];

    /**
    * @param ValidatableInterface $model The object to validate.
    */
    public function __construct(ValidatableInterface $model)
    {
        $this->model = $model;
    }

    /**
    * @param string      $msg   The error message.
    * @param string|null $ident Optional result ident.
    * @return ValidatorInterface
    */
    public function error($msg, $ident = null)
    {
        return $this->log(self::ERROR, $msg, $ident);
    }

    /**
    * @param string      $msg   The warning message.
    * @param string|null $ident Optional result ident.
    * @return ValidatorInterface
    */
    public function warning($msg, $ident = null)
    {
        return $this->log(self::WARNING, $msg, $ident);
    }

    /**
    * @param string      $msg   The notice message.
    * @param string|null $ident Optional result ident.
    * @return ValidatorInterface
    */
    public function notice($msg, $ident = null)
    {
        return $this->log(self::NOTICE, $msg, $ident);
    }

    /**
    * @param string      $level The validation level.
    * @param string      $msg   The validation message.
    * @param string|null $ident Optional result ident.
    * @return ValidatorInterface
    */
    public function log($level, $msg, $ident = null)
    {
        $this->addResult(
            [
                'ident'   => (($ident !== null) ? $ident : ''),
                'level'   => $level,
                'message' => $msg
            ]
        );
        return $this;
    }

    /**
    * @param array|ValidatorResult $result The result object or array.
    * @throws InvalidArgumentException If result is not an array or object.
    * @return AbstractValidator Chainable
    */
    public function addResult($result)
    {
        if (is_array($result)) {
            $result = new ValidatorResult($result);
        } elseif (!($result instanceof ValidatorResult)) {
            throw new InvalidArgumentException(
                'Result must be an array or a ValidatorResult object.'
            );
        }
        $level = $result->level();
        if (!isset($this->results[$level])) {
            $this->results[$level] = [];
        }
        $this->results[$level][] = $result;
        return $this;
    }

    /**
    * @return array
    */
    public function results()
    {
        return $this->results;
    }

    /**
    * @return array
    */
    public function errorResults()
    {
        if (!isset($this->results[self::ERROR])) {
            return [];
        }
        return $this->results[self::ERROR];
    }

    /**
    * @return array
    */
    public function warningResults()
    {
        if (!isset($this->results[self::WARNING])) {
            return [];
        }
        return $this->results[self::WARNING];
    }

    /**
    * @return array
    */
    public function noticeResults()
    {
        if (!isset($this->results[self::NOTICE])) {
            return [];
        }
        return $this->results[self::NOTICE];
    }

    /**
    * @param ValidatorInterface $v            The validator to merge.
    * @param string             $ident_prefix Optional identigfier prefix.
    * @return ValidatorInterface Chainable
    */
    public function merge(ValidatorInterface $v, $ident_prefix = null)
    {
        $results = $v->results();
        foreach ($results as $level => $levelResults) {
            foreach ($levelResults as $r) {
                if ($ident_prefix !== null) {
                    $r->set_ident($ident_prefix.'.'.$r->ident());
                }
                $this->addResult($r);
            }
        }
        return $this;
    }

    /**
    * @return boolean
    */
    abstract public function validate();
}
