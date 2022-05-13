<?php

namespace Charcoal\Validator;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * A Validator Result object.
 */
class ValidatorResult
{
    /**
     * @var string $ident
     */
    private $ident;

    /**
     * Can be `notice`, `warning` or `error`
     * @var string $level
     */
    private $level;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @var DateTimeInterface $ts
     */
    private $ts;

    /**
     * @param array|\ArrayAccess $data Optional data.
     */
    public function __construct($data = null)
    {
        $ts = new DateTime();
        $this->setTs($ts);

        if (is_array($data) || ($data instanceof \ArrayAccess)) {
            $this->setData($data);
        }
    }

    /**
     * @param array $data The validator result data.
     * @return self
     */
    public function setData(array $data)
    {
        if (isset($data['ident'])) {
            $this->setIdent($data['ident']);
        }
        if (isset($data['level']) && $data['message'] !== null) {
            $this->setLevel($data['level']);
        }
        if (isset($data['message']) && $data['message'] !== null) {
            $this->setMessage($data['message']);
        }
        if (isset($data['ts']) && $data['ts'] !== null) {
            $this->setTs($data['ts']);
        }
        return $this;
    }

    /**
     * @param string $ident The result identigier.
     * @throws InvalidArgumentException If parameter is not valid.
     * @return ValidatorResult
     */
    public function setIdent($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident must be a string.'
            );
        }
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $level The validation level ('notice', 'warning' or 'error').
     * @throws InvalidArgumentException If parameter is not a valid level.
     * @return ValidatorResult
     */
    public function setLevel($level)
    {
        if (!is_string($level)) {
            throw new InvalidArgumentException(
                'Level must be a string.'
            );
        }
        if (!in_array($level, ['notice', 'warning', 'error'])) {
            throw new InvalidArgumentException(
                'Level can only be notice, warning or error.'
            );
        }
        $this->level = $level;
        return $this;
    }

    /**
     * @return string
     */
    public function level()
    {
        return $this->level;
    }

    /**
     * @param string $message The validation message.
     * @throws InvalidArgumentException If parameter is not valid.
     * @return ValidatorResult
     */
    public function setMessage($message)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException(
                'Message must be a string.'
            );
        }
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * @param string|DateTime $ts The datetime value.
     * @throws InvalidArgumentException If parameter is not valid "datetime".
     * @return ValidatorResult
     */
    public function setTs($ts)
    {
        if (is_string($ts)) {
            $ts = new DateTime($ts);
        }
        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Timestamp" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->ts = $ts;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function ts()
    {
        return $this->ts;
    }
}
