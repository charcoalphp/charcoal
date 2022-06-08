<?php

declare(strict_types=1);

namespace Charcoal\Email;

use InvalidArgumentException;

use Charcoal\Email\Services\Parser;

/**
 * For objects that are or interact with emails.
 */
trait EmailAwareTrait
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @param Parser $parser Email parser service.
     * @return void
     */
    protected function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * @return Parser
     */
    protected function getParser(): Parser
    {
        if ($this->parser === null) {
            $this->parser = new Parser();
        }
        return $this->parser;
    }

    /**
     * @param mixed $email An email value (either a string or an array).
     * @throws InvalidArgumentException If the email is invalid.
     * @return string
     */
    protected function parseEmail($email): string
    {
        return $this->getParser()->parse($email);
    }

    /**
     * Convert an email address (RFC822) into a proper array notation.
     *
     * @param  mixed $var An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email is invalid.
     * @return array|null
     */
    protected function emailToArray($var) : ?array
    {
        return $this->getParser()->emailToArray($var);
    }

    /**
     * Convert an email address array to a RFC-822 string notation.
     *
     * @param  array $arr An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email array is invalid.
     * @return string
     */
    protected function emailFromArray(array $arr) : string
    {
        return $this->getParser()->emailFromArray($arr);
    }
}
