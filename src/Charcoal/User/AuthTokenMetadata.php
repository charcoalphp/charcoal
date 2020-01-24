<?php

namespace Charcoal\User;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\ModelMetadata;

/**
 * User Auth Token Metadata
 */
class AuthTokenMetadata extends ModelMetadata
{
    /**
     * @var boolean $enabled
     */
    private $enabled;

    /**
     * @var boolean $httpsOnly
     */
    private $httpsOnly;

    /**
     * @var string $tokenName
     */
    private $tokenName;

    /**
     * @var string $tokenDuration
     */
    private $tokenDuration;

    /**
     * @see \Charcoal\Config\ConfigInterface::defaults()
     *
     * @return array
     */
    public function defaults()
    {
        $parentDefaults = parent::defaults();

        $defaults = array_replace_recursive($parentDefaults, [
            'enabled'        => true,
            'token_name'     => 'charcoal_user_login',
            'token_duration' => '15 days',
            'token_path'     => '',
            'https_only'     => false,
        ]);
        return $defaults;
    }

    /**
     * @param  boolean $enabled The enabled flag.
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = !!$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param  boolean $httpsOnly The "HTTPS only" flag.
     * @return self
     */
    public function setHttpsOnly($httpsOnly)
    {
        $this->httpsOnly = !!$httpsOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHttpsOnly()
    {
        return $this->httpsOnly;
    }

    /**
     * @param  string $name The token name.
     * @throws InvalidArgumentException If the token name is not a string.
     * @return self
     */
    public function setTokenName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Can not set auth token\'s name: must be a string'
            );
        }
        $this->tokenName = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * @param  string $duration The token duration, or duration. Ex: "15 days".
     * @throws InvalidArgumentException If the token name is not a string.
     * @return self
     */
    public function setTokenDuration($duration)
    {
        if (!is_string($duration)) {
            throw new InvalidArgumentException(
                'Can not set auth token\'s duration: must be a string'
            );
        }
        $this->tokenDuration = $duration;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenDuration()
    {
        return $this->tokenDuration;
    }

    /**
     * @deprecated In favour of {@see self::setTokenName()}.
     *
     * @param  string $name The cookie name.
     * @return self
     */
    public function setCookieName($name)
    {
        trigger_error(
            'Auth token option "cookie_name" is deprecated in favour of "token_name"',
            E_USER_DEPRECATED
        );

        $this->setTokenName($name);
        return $this;
    }

    /**
     * @deprecated In favour of {@see self::getTokenName()}.
     *
     * @return string
     */
    public function getCookieName()
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        return $this->getTokenDuration();
    }

    /**
     * @deprecated In favour of {@see self::setTokenDuration()}.
     *
     * @param  string $duration The cookie duration, or duration. Ex: "15 days".
     * @return self
     */
    public function setCookieDuration($duration)
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        $this->setTokenDuration($duration);
        return $this;
    }

    /**
     * @deprecated In favour of {@see self::getTokenDuration()}.
     *
     * @return string
     */
    public function getCookieDuration()
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        return $this->getTokenDuration();
    }
}
