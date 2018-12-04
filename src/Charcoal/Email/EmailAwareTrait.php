<?php

namespace Charcoal\Email;

use InvalidArgumentException;

/**
 * For objects that are or interact with emails.
 */
trait EmailAwareTrait
{

    /**
     * @param mixed $email An email value (either a string or an array).
     * @throws InvalidArgumentException If the email is invalid.
     * @return string
     */
    protected function parseEmail($email)
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
     * @param  mixed $var An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email is invalid.
     * @return array|null
     */
    protected function emailToArray($var)
    {
        if ($var === null) {
            return null;
        }
        if (!is_string($var) && !is_array($var)) {
            throw new InvalidArgumentException(
                sprintf('Email address must be an array or a string. (%s given)', gettype($var))
            );
        }

        // Assuming nobody's gonna set an email that is just a display name
        if (is_string($var)) {
            $regexp = '/([\w\s\'\"-_]+[\s]+)?(<)?(([\w-\._]+)@((?:[\w-_]+\.)+)([a-zA-Z]{2,4}))?(>)?/u';
            preg_match($regexp, $var, $out);
            $arr = [
                'email' => (isset($out[3]) ? trim($out[3]) : ''),
                'name'  => (isset($out[1]) ? trim(trim($out[1]), '\'"') : '')
            ];
        } else {
            $arr = $var;
        }

        if (!isset($arr['name'])) {
            $arr['name'] = '';
        }

        return $arr;
    }

    /**
     * Convert an email address array to a RFC-822 string notation.
     *
     * @param  array $arr An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email array is invalid.
     * @return string
     */
    protected function emailFromArray(array $arr)
    {
        if (isset($arr['address'])) {
            $arr['email'] = $arr['address'];
            unset($arr['address']);
        }

        if (!isset($arr['email'])) {
            throw new InvalidArgumentException(
                'The array must contain at least the "address" key.'
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
