<?php

namespace Charcoal\Core;

use \InvalidArgumentException as InvalidArgumentException;

/**
*
*/
class StringFormat
{
    /**
    * Holds the string to format.
    *
    * @var string $_string
    */
    protected $_string = '';

    /**
    * Supports unicode or no.
    *
    * @var boolean $_unicode
    */
    protected $_unicode = true;

    /**
    * @param string $string Optional string
    */
    public function __construct($string = null)
    {
        if ($string !== null) {
            $this->set_string($string);
        }
    }

    /**
    * @return string
    */
    public function __toString()
    {
        return $this->string();
    }

    /**
    * @param string $string
    * @throws InvalidArgumentException
    * @return StringFormat Chainable
    */
    public function set_string($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException('String must be a string');
        }
        $this->_string = $string;
        return $this;
    }

    /**
    * @return string
    */
    public function string()
    {
        return $this->_string;
    }

    /**
    * @param boolean $unicode
    * @throws InvalidArgumentException
    * @return StringFormat Chainable
    */
    public function set_unicode($unicode)
    {
        if (!is_bool($unicode)) {
            throw new InvalidArgumentException('Unicode must be a boolean');
        }
        $this->_unicode = $unicode;
        return $this;
    }

    /**
    * @return boolean
    */
    public function unicode()
    {
        return $this->_unicode;
    }

    /**
    * Strip all HTML tags.
    * @return StringFormat Chainable
    */
    public function strip_tags()
    {
        $this->_string = strip_tags($this->_string);
        return $this;
    }

    /**
    * Replace accents with their non-accents counterpats.
    * @return StringFormat Chainable
    */
    public function unaccents()
    {
        $str = htmlentities($this->_string, ENT_COMPAT, 'UTF-8');
        $this->_string = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|ring|caron|slash);/', '$1', $str);
        $this->_string = preg_replace('/&([a-zA-Z]{2})(lig);/', '$1', $this->_string);
        $this->_string = html_entity_decode($this->_string);
        return $this;
    }

    /**
    * Strip all non-alphanumeric characters from string.
    *
    * The definietion of alphanumeric is:
    * - In unicode: All letters and numbers
    * - In non-unicode:
    *   -Only the 26 letters of the english alphabets (a to z, no accents)
    *   - Either uppercase or lowercase
    *   - Plus the number 0 to 9
    *
    * Ideally, should be used with unaccents
    *
    * @return StringFormat Chainable
    */
    public function alphanumeric()
    {
        if ($this->unicode()) {
            $this->_string = preg_replace("/[^[:alnum:][:space:]]/ui", '', $this->_string);
        } else {
            $this->_string = preg_replace("/[^A-Za-z0-9 ]/", '', $this->_string);
        }
        return $this;
    }
}
