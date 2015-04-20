<?php

namespace Charcoal\Validator;

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
    public $ident;
    /**
    * Can be `notice`, `warning` or `error`
    * @var string $level
    */
    public $level;
    /**
    * @var string $message
    */
    public $message;
    /**
    * @var DateTime $ts
    */
    public $ts;

    public function __construct($data = null)
    {
        $ts = new \DateTime();
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

    public function set_ident($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    public function ident()
    {
        return $this->ident;
    }

    public function set_level($level)
    {
        $this->level = $level;
        return $this;
    }

    public function level()
    {
        return $this->level;
    }

    public function set_message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function message()
    {
        return $this->message;
    }

    public function set_ts($ts)
    {
        if (is_string($ts)) {
            $ts = new \Datetime($ts);
        }
        if (!($ts instanceof \Datetime)) {
            throw new \InvalidArgumentException('ts must be a datetime / string.');
        }
        $this->ts = $ts;
        return $this;
    }

    public function ts()
    {
        return $this->ts;
    }
}
