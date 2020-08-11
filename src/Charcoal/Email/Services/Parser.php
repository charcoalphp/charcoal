<?php

declare(strict_types=1);

namespace Charcoal\Email\Services;

use InvalidArgumentException;

/**
 * Parser service
 */
class Parser
{
    /**
     * @var string
     */
    public const REGEXP = '/(.+[\s]+)?(<)?(([\w\-\._\+]+)@((?:[\w\-_]+\.)+)([a-zA-Z]*))?(>)?/u';

    /**
     * @param string|array $email An email value (either a string or an array).
     * @throws InvalidArgumentException If the email is invalid.
     * @return string
     */
    public function parse($email): string
    {
        if (is_array($email)) {
            return $this->emailFromArray($email);
        } elseif (is_string($email)) {
            $arr = $this->emailToArray($email);
            return $this->emailFromArray($arr);
        } else {
            throw new InvalidArgumentException(
                'Can not parse email: must be an array or a string'
            );
        }
    }

    /**
     * Convert an email address (RFC822) into a proper array notation.
     *
     * @param  string $var An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email is invalid.
     * @return array
     */
    public function emailToArray(string $var) : array
    {
        preg_match(self::REGEXP, $var, $out);
        return [
            'email' => (isset($out[3]) ? trim($out[3]) : ''),
            'name'  => (isset($out[1]) ? trim(trim($out[1]), '\'"') : '')
        ];
    }

    /**
     * Convert an email address array to a RFC-822 string notation.
     *
     * @param  array $arr An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email array is invalid.
     * @return string
     */
    public function emailFromArray(array $arr) : string
    {
        if (!isset($arr['email'])) {
            throw new InvalidArgumentException(
                'The array must contain at least the "email" key.'
            );
        }

        $email = strval(filter_var($arr['email'], FILTER_SANITIZE_EMAIL));

        if (!isset($arr['name']) || $arr['name'] === '') {
            return $email;
        }

        $name = str_replace('"', '', filter_var($arr['name'], FILTER_SANITIZE_STRING));
        return sprintf('"%s" <%s>', $name, $email);
    }
}
