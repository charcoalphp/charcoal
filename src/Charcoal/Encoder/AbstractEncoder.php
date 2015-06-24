<?php

namespace Charcoal\Encoder;

use \InvalidArgumentException as InvalidArgumentException;

use \Charcoal\Encoder\EncoderInterface as EncoderInterface;

/**
* A default implementation, as abstract class, of `EncoderInterface`.
*/
abstract class AbstractEncoder implements EncoderInterface
{
    /**
    * @var string $_salt
    */
    private $_salt = '';

    /**
    * @param string $salt
    * @throws InvalidArgumentException
    * @return AbstractEncoder Chainable
    */
    public function set_salt($salt)
    {
        if (!is_string($salt)) {
            throw new InvalidArgumentException('Salt must be a string');
        }
        $this->_salt = $salt;
        return $this;
    }

    /**
    * @return string
    */
    public function salt()
    {
        return $this->_salt;
    }

    /**
    * Encode a string.
    *
    * @param string $plain_string The (plain) string to encode.
    * @param string $salt         Optional salt.
    * @return string The encoded string.
    */
    abstract public function encode($plain_string, $salt = '');

    /**
    * Decode an encoded string.
    *
    * @param string $encoded_string The (encoded) string to decode.
    * @param string $salt           Optional salt.
    * @return string The decoded (original) string.
    */
    abstract public function decode($encoded_string, $salt = '');

    /**
    * Checks wether an encoded string hash matches a plain string.
    *
    * Note that the `==` check can be vulnerable to advanced timing attacks.
    * Validation should be done by underlying library when available.
    *
    * @param string $encoded_string The (encoded) string hash to verify.
    * @param string $plain_string   The plain string to match against.
    * @param string $salt           Optional salt.
    * @return boolean True if strings match, false if not
    */
    public function match($encoded_string, $plain_string, $salt = '')
    {
        return ($plain_string === $this->decode($encoded_string, $salt));
    }
}
