<?php

namespace Charcoal\Validator;

use \Datetime as Datetime;

/**
* A Validator Result object.
*
* @todo Change the visibility of the members to private. (Require custom encoder to output them)
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
    * @var DateTime $ts
    */
    private $ts;

    public function __construct($data = null)
    {
        $ts = new DateTime();
        $this->set_ts($ts);

        if ($data !== null) {
            $this->set_data($data);
        }
    }

    /**
    * @var array $data
    * @throws \InvalidArgumentException if data is not an array
    * @return Result Chainable
    */
    public function set_data($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array.');
        }

        if (isset($data['ident'])) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['level']) && $data['message'] !== null) {
            $this->set_level($data['level']);
        }
        if (isset($data['message']) && $data['message'] !== null) {
            $this->set_message($data['message']);
        }
        if (isset($data['ts']) && $data['ts'] !== null) {
            $this->set_ts($data['ts']);
        }
        return $this;
    }

    /**
    * @var string $ident
    * @throws \InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new \InvalidArgumentException('Ident must be a string');
        }
        $this->ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->ident;
    }

    /**
    * @var string $level
    * @throws \InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_level($level)
    {
        if (!is_string($level)) {
            throw new \InvalidArgumentException('Level must be a string');
        }
        if (!in_array($level, ['notice', 'warning', 'error'])) {
            throw new \InvalidArgumentException('Level can only be notice, warning or error');
        }
        $this->level = $level;
        return $this;
    }

    public function level()
    {
        return $this->level;
    }

    /**
    * @var string $message
    * @throws \InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_message($message)
    {
        if (!is_string($message)) {
            throw new \InvalidArgumentException('Message must be a string');
        }
        $this->message = $message;
        return $this;
    }

    public function message()
    {
        return $this->message;
    }

    /**
    * @var string|Datetime $ident
    * @throws \InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_ts($ts)
    {
        if (is_string($ts)) {
            $ts = new Datetime($ts);
        }
        if (!($ts instanceof Datetime)) {
            throw new \InvalidArgumentException('ts must be a datetime / valid string.');
        }
        $this->ts = $ts;
        return $this;
    }

    public function ts()
    {
        return $this->ts;
    }
}
