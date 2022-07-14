<?php

namespace Charcoal\Validator;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Validator\ValidatorInterface;
use Charcoal\Validator\ValidatableInterface;
use Charcoal\Validator\ValidatorResult;

/**
 * An abstract class that implements most of ValidatorInterface.
 *
 * The only abstract method in the class is `validate()`
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var ValidatableInterface
     */
    protected $model;

    /**
     * @var ValidatorResult[] $results
     */
    private $results = [];

    /**
     * Holds a list of all camelized strings.
     *
     * @var string[]
     */
    protected static $camelCache = [];

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
     * @return self
     */
    public function error($msg, $ident = null)
    {
        return $this->log(self::ERROR, $msg, $ident);
    }

    /**
     * @param string      $msg   The warning message.
     * @param string|null $ident Optional result ident.
     * @return self
     */
    public function warning($msg, $ident = null)
    {
        return $this->log(self::WARNING, $msg, $ident);
    }

    /**
     * @param string      $msg   The notice message.
     * @param string|null $ident Optional result ident.
     * @return self
     */
    public function notice($msg, $ident = null)
    {
        return $this->log(self::NOTICE, $msg, $ident);
    }

    /**
     * @param string      $level The validation level.
     * @param string      $msg   The validation message.
     * @param string|null $ident Optional result ident.
     * @return self
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
     * @return self
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
     * @param  ValidatorInterface $v      The validator to merge.
     * @param  string|null        $prefix Optional key to prefix onto identifiers.
     * @return self
     */
    public function merge(ValidatorInterface $v, $prefix = null)
    {
        $allResults = $v->results();

        foreach ($allResults as $level => $resultset) {
            foreach ($resultset as $result) {
                if ($prefix !== null) {
                    $result->setIdent($prefix . '.' . $result->ident());
                }
                $this->addResult($result);
            }
        }
        return $this;
    }

    /**
     * Transform a string from "snake_case" to "camelCase".
     *
     * @param  string $value The string to camelize.
     * @return string The camelized string.
     */
    final protected function camelize($value)
    {
        $key = $value;

        if (isset(static::$camelCache[$key])) {
            return static::$camelCache[$key];
        }

        if (strpos($value, '_') !== false) {
            $value = implode('', array_map('ucfirst', explode('_', $value)));
        }

        static::$camelCache[$key] = lcfirst($value);

        return static::$camelCache[$key];
    }

    /**
     * @return boolean
     */
    abstract public function validate();
}
