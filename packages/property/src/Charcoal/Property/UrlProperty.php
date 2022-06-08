<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * URL Property
 */
class UrlProperty extends StringProperty
{
    /**
     * Regular Expression for validating a URL.
     *
     * @link https://mathiasbynens.be/demo/url-regex In search for the perfect regular expression
     * @link https://gist.github.com/729294 Using Diego Perini's version
     * @var DEFAULT_REGEXP
     */
    const DIEGO_PERINI_PATTERN = '_^
        (?:(?<scheme>https?|ftp)://)
        (?:
            (?<user>\S+)
            (?::(?<pass>\S*))?
        @)?
        (?<host>
            (?!(?:10|127)(?:\.\d{1,3}){3})
            (?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})
            (?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})
            (?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])
            (?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}
            (?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))
        |
            (?:(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)
            (?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]-*)*[a-z\x{00a1}-\x{ffff}0-9]+)*
            (?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,}))
            \.?
        )
        (?<port>:\d{2,5})?
        (?:(?=\/?)
            (?<path>\/[^\?\#\s[:cntrl:]]*)?
            (?:\?(?<query>[^\#\s[:cntrl:]]*))?
            (?:\#(?<fragment>[^\s[:cntrl:]]*))?
        )?
    $_iuSx';

    const DEFAULT_URL_PATTERN = self::DIEGO_PERINI_PATTERN;

    /**
     * @return string
     */
    public function type()
    {
        return 'url';
    }

    /**
     * Parse a value. (From `AbstractProperty`)
     *
     * Ensure the URL is valid (sanitize).
     *
     * @param mixed $val A single value to parse.
     * @return mixed The parsed value.
     */
    public function parseOne($val)
    {
        return filter_var(strip_tags($val), FILTER_SANITIZE_URL);
    }
}
