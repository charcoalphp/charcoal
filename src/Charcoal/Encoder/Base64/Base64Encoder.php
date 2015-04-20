<?php

namespace Charcoal\Encoder\Base64;

use \Charcoal\Encoder\AbstractEncoder as AbstractEncoder;

/**
* Base64 encoding / decoding of string.
*
* Note that the base64 encoder is not using any type of cryptography and is therefore
* absolutely not suited for sensitive data.
*/
class Base64Encoder extends AbstractEncoder
{
    /**
    * Encode a string.
    *
    * @param string $plain_string The (plain) string to encode.
    * @param string $salt Optional salt.
    * @throws \InvalidArgumentException If plain_string is not a string
    * @return string The encoded string.
    */
    public function encode($plain_string, $salt = '')
    {
        if (!is_string($plain_string)) {
            throw new \InvalidArgumentException('Plain string must be a string');
        }
        $encoded = '';
        $key = $salt;
        $length = strlen($plain_string);
        for ($i=0; $i<$length; $i++) {
            $char = substr($plain_string, $i, 1);
            $keychar = ($key) ? substr($key, (($i % strlen($key))-1), 1) : '';
            $char = chr(ord($char)+ord($keychar));
            $encoded .= $char;
        }
        $encoded = base64_encode($encoded);
        return $encoded;
    }

    /**
    * Decode an encoded string.
    *
    * @param string $encoded_string The (encoded) string to decode.
    * @param string $salt Optional salt.
    * @throws \InvalidArgumentException If encoded_string is not a string
    * @return string The decoded (original) string.
    */
    public function decode($encoded_string, $salt = '')
    {
        if (!is_string($encoded_string)) {
            throw new \InvalidArgumentException('Encoded string must be a string');
        }
        $decoded = '';
        $key = $salt;
        $string = base64_decode($encoded_string);
        $length = strlen($string);
        for ($i=0; $i<$length; $i++) {
            $char = substr($string, $i, 1);
            $keychar = ($key) ? substr($key, (($i % strlen($key))-1), 1) : '';
            $char = chr(ord($char)-ord($keychar));
            $decoded .= $char;
        }
        return $decoded;
    }
}
