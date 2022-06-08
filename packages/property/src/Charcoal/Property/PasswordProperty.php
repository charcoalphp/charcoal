<?php

namespace Charcoal\Property;

// From 'charcoal-property'
use Charcoal\Property\StringProperty;

/**
 * Password Property
 *
 * The password property is a specialized string property meant to store encrypted passwords.
 */
class PasswordProperty extends StringProperty
{
    /**
     * @return string
     */
    public function type()
    {
        return 'password';
    }

    /**
     * Overrides the StringProperty::save() method to ensure the value is encrypted.
     *
     * If the hash is corruped or the algorithm is not recognized, the value will be rehashed.
     *
     * @todo   Implement proper hashing/rehashing/validation.
     * @param  mixed $val The value, at time of saving.
     * @return string
     */
    public function save($val)
    {
        if ($val === null || $val === '') {
            return $val;
        }

        if (!$this->isHashed($val)) {
            $val = password_hash($val, PASSWORD_DEFAULT);
        }

        return $val;
    }

    /**
     * Retrieve the maximum number of characters allowed.
     *
     * @return integer
     */
    public function getMaxLength()
    {
        if (PASSWORD_DEFAULT === PASSWORD_BCRYPT) {
            /** @link https://www.php.net/manual/en/function.password-hash.php */
            return 72;
        }

        return parent::getMaxLength();
    }

    /**
     * Determine if the given value is hashed.
     *
     * If the hash is corruped or the algorithm is not recognized, the value is assumed to be plain-text (not hashed).
     *
     * @param  string $hash The value to test.
     * @return boolean
     */
    public function isHashed($hash)
    {
        $info = password_get_info($hash);
        return strtolower($info['algoName']) !== 'unknown';
    }

    /**
     * Validates password and rehashes if necessary.
     *
     * If the hash is corruped or the algorithm is not recognized, the value is assumed to be plain-text (not hashed).
     *
     * @param  string $password A plain-text password.
     * @param  string $hash     A hash created by {@see password_hash()}.
     * @return string|boolean
     */
    public function isValid($password, $hash)
    {
        if (password_verify($password, $hash) === false) {
            return false;
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        return $hash;
    }
}
