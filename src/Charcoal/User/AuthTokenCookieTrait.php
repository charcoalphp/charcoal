<?php

namespace Charcoal\User;

/**
 *
 */
trait AuthTokenCookieTrait
{
    /**
     * @return boolean
     */
    public function sendCookie()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $metadata = $this->metadata();

        $name   = $metadata['tokenName'];
        $value  = $this['ident'].';'.$this['token'];
        $expiry = isset($this['expiry']) ? $this['expiry']->getTimestamp() : null;
        $path   = $metadata['tokenPath'];
        $secure = $metadata['httpsOnly'];

        return setcookie($name, $value, $expiry, $path, '', $secure);
    }

    /**
     * @return boolean
     */
    public function deleteCookie()
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $metadata = $this->metadata();

        $name   = $metadata['tokenName'];
        $expiry = (time() - 1000);
        $path   = $metadata['tokenPath'];
        $secure = $metadata['httpsOnly'];

        return setcookie($name, '', $expiry, $path, '', $secure);
    }

    /**
     * @return array|null `[ 'ident' => '', 'token' => '' ]
     */
    public function getTokenDataFromCookie()
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $metadata = $this->metadata();

        $name = $metadata['tokenName'];
        if (!isset($_COOKIE[$name])) {
            return null;
        }

        $cookie = $_COOKIE[$name];
        $data   = array_pad(explode(';', $cookie), 2, null);
        if (!isset($data[0]) || !isset($data[1])) {
            return null;
        }

        return [
            'ident' => $data[0],
            'token' => $data[1],
        ];
    }

    /**
     * Determine if authentication by token is supported.
     *
     * @return boolean
     */
    abstract public function isEnabled();

    /**
     * @return \Charcoal\Model\MetadataInterface
     */
    abstract public function metadata();
}
