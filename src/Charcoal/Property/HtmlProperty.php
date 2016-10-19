<?php

namespace Charcoal\Property;

use \InvalidArgumentException;

// Local namespace dependencies
use \Charcoal\Property\StringProperty;

/**
 * HTML Property.
 *
 * The html property is a specialized string property.
 */
class HtmlProperty extends StringProperty
{

    /**
     * @return string
     */
    public function type()
    {
        return 'html';
    }

    /**
     * Parse the HTML.
     *
     * @param mixed $val Value to be parsed.
     * @return mixed
     */
    public function parseVal($val)
    {
        if (is_array($val)) {
            foreach ($val as $i => $v) {
                $val[$i] = $this->parseVal($val);
            }
        }

        if (is_string($val)) {
            $val = $this->parseRelativeURIs($val);
        }

        return $val;
    }

    /**
     * Converts relative URIs to be prefixed by "/".
     *
     * @param  string $str A string to format.
     * @throws InvalidArgumentException If the given variable is not a string.
     * @return string
     */
    protected function parseRelativeURIs($str)
    {
        static $search;

        if (!is_string($str)) {
            throw new InvalidArgumentException(
                '$str must be a string.'
            );
        }

        if ($search === null) {
            $attr = [ 'href', 'link', 'url', 'src' ];
            $uri  = [ '../', './', '/', 'data', 'fax', 'file', 'ftp', 'geo', 'http', 'mailto', 'sip', 'tag', 'tel', 'urn' ];

            $search = sprintf(
                '(?<=%1$s=["\'])(?!%2$s)(\S+)(?=["\'])',
                implode('=["\']|', array_map('preg_quote', $attr)),
                implode('|', array_map('preg_quote', $uri))
            );
        }

        return preg_replace('~'.$search.'~i', '/$1', $str);
    }

    /**
     * Retrieve the property's value in a format suitable for storage.
     *
     * @param  mixed $val The value to convert for storage.
     * @return mixed
     */
    public function storageVal($val)
    {
        $val = parent::storageVal($val);

        return $this->parseVal($val);
    }

    /**
     * Unlike strings' default upper limit of 255, HTML has no default max length (0).
     *
     * @return integer
     */
    public function defaultMaxLength()
    {
        return 0;
    }

    /**
     * Get the SQL type (Storage format).
     *
     * @return string The SQL type
     */
    public function sqlType()
    {
        return 'TEXT';
    }
}
