<?php

namespace Charcoal\Email;

// From `PHP`
use \InvalidArgumentException;

/**
 * For objects that are or interact with emails.
 */
trait EmailAwareTrait
{
    /**
     * Convert an email address into a proper array notation.
     *
     * @param  mixed $var An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email is invalid.
     * @return string
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
            // @todo Validation
            $arr = [
                'email' => $var,
                'name'  => ''
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
     * Convert an email address array to a string notation.
     *
     * @param  array $arr An email array (containing an "email" key and optionally a "name" key).
     * @throws InvalidArgumentException If the email array is invalid.
     * @return string
     */
    protected function emailFromArray(array $arr)
    {
        if (!isset($arr['email'])) {
            throw new InvalidArgumentException(
                'The array must contain at least the "email" key.'
            );
        }

        $email = filter_var($arr['email'], FILTER_SANITIZE_EMAIL);
        if (!isset($arr['name'])) {
            return $email;
        }

        $name = str_replace('"', '', filter_var($arr['name'], FILTER_SANITIZE_STRING));
        return sprintf('%s <%s>', $name, $email);
    }
}
