<?php

namespace Charcoal\Encoder;

/**
*
*/
interface EncoderInterface
{
    /**
    * Encode a string.
    *
    * @param string $plainString The (plain) string to encode.
    * @param string $salt         Optional salt.
    * @return string The encoded string hash.
    */
    public function encode($plainString, $salt = '');

    /**
    * Decode an encoded string.
    *
    * @param string $encodedString The (encoded) string hash to decode.
    * @param string $salt           Optional salt.
    * @return string The decoded (original) string.
    */
    public function decode($encodedString, $salt = '');

    /**
    * Checks wether an encoded string hash matches a plain string.
    *
    * @param string $encodedString The (encoded) string hash to verify.
    * @param string $plainString   The plain string to match against.
    * @param string $salt           Optional salt.
    * @return boolean True if strings match, false if not
    */
    public function match($encodedString, $plainString, $salt = '');
}
