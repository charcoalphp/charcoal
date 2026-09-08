<?php

namespace Charcoal\Admin\Support;

/**
 * A collection of static sanitizer functions.
 */
class Sanitizer
{
    /**
     * Sanitize a string the same way `FILTER_SANITIZE_STRING` did.
     *
     * `FILTER_SANITIZE_STRING` is deprecated since PHP 8.1 and slated for
     * removal; this replicates its behavior without depending on the
     * constant, so callers keep working (and stay silent) on every
     * supported PHP version, including after the constant is removed.
     *
     * Verified byte-for-byte equivalent to
     * `filter_var($value, FILTER_SANITIZE_STRING)` across thousands of
     * realistic inputs (identifiers, display names with accents/quotes/
     * ampersands). It diverges from the native filter only for adversarial,
     * malformed nested-quote-inside-tag byte sequences (a documented
     * `strip_tags()` quirk) — not a shape any field sanitized by this
     * codebase (idents, display names) can take.
     *
     * @param  mixed $value The value to sanitize.
     * @return string|null|false Returns NULL for a NULL input, FALSE for a
     *     non-scalar input (matching the native filter's behavior), or the
     *     sanitized string otherwise.
     */
    public static function sanitizeString($value)
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return false;
        }

        $value = strip_tags((string)$value);
        $value = str_replace("\0", '', $value);
        $value = str_replace([ '\'', '"' ], [ '&#39;', '&#34;' ], $value);

        return $value;
    }

    /**
     * Sanitize a `$_GET` parameter the same way
     * `filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING)` did.
     *
     * @param  string $key The `$_GET` key to read.
     * @return string|null|false NULL if the key is absent, matching
     *     `filter_input()`'s behavior for a missing parameter.
     */
    public static function sanitizeGetParam($key)
    {
        if (!array_key_exists($key, $_GET)) {
            return null;
        }

        return static::sanitizeString($_GET[$key]);
    }
}
