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
    private ?bool $enabled = null;

    private ?bool $httpsOnly = null;

    private ?string $tokenName = null;

    private ?string $tokenDuration = null;

    /**
     * @see \Charcoal\Config\ConfigInterface::defaults()
     */
    #[\Override]
    public function defaults(): array
    {
        $parentDefaults = parent::defaults();
        return array_replace_recursive($parentDefaults, [
            'enabled'        => true,
            'token_name'     => 'charcoal_user_login',
            'token_duration' => '15 days',
            'token_path'     => '',
            'https_only'     => false,
        ]);
    }

    /**
     * @param  boolean $enabled The enabled flag.
     */
    public function setEnabled($enabled): static
    {
        $this->enabled = (bool)$enabled;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param  boolean $httpsOnly The "HTTPS only" flag.
     */
    public function setHttpsOnly($httpsOnly): static
    {
        $this->httpsOnly = (bool)$httpsOnly;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHttpsOnly(): ?bool
    {
        return $this->httpsOnly;
    }

    /**
     * @param  string $name The token name.
     * @throws InvalidArgumentException If the token name is not a string.
     */
    public function setTokenName($name): static
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
    public function getTokenName(): ?string
    {
        return $this->tokenName;
    }

    /**
     * @param  string $duration The token duration, or duration. Ex: "15 days".
     * @throws InvalidArgumentException If the token name is not a string.
     */
    public function setTokenDuration($duration): static
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
    public function getTokenDuration(): ?string
    {
        return $this->tokenDuration;
    }

    /**
     *
     * @param  string $name The cookie name.
     */
    #[\Deprecated(message: 'In favour of {@see self::setTokenName()}.')]
    public function setCookieName($name): static
    {
        trigger_error(
            'Auth token option "cookie_name" is deprecated in favour of "token_name"',
            E_USER_DEPRECATED
        );

        $this->setTokenName($name);
        return $this;
    }

    /**
     * @return string
     */
    #[\Deprecated(message: 'In favour of {@see self::getTokenName()}.')]
    public function getCookieName(): ?string
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        return $this->getTokenDuration();
    }

    /**
     *
     * @param  string $duration The cookie duration, or duration. Ex: "15 days".
     */
    #[\Deprecated(message: 'In favour of {@see self::setTokenDuration()}.')]
    public function setCookieDuration($duration): static
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        $this->setTokenDuration($duration);
        return $this;
    }

    /**
     * @return string
     */
    #[\Deprecated(message: 'In favour of {@see self::getTokenDuration()}.')]
    public function getCookieDuration(): ?string
    {
        trigger_error(
            'Auth token option "cookie_duration" is deprecated in favour of "token_duration"',
            E_USER_DEPRECATED
        );

        return $this->getTokenDuration();
    }
}
