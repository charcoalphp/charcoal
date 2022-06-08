<?php

namespace Charcoal\User;

/**
 *
 */
interface AuthTokenCookieInterface
{
    /**
     * @return boolean Success / failure.
     */
    public function sendCookie();

    /**
     * @return boolean Success / failure.
     */
    public function deleteCookie();

    /**
     * @return array|null `[ 'ident' => '', 'token' => '' ]
     */
    public function getTokenDataFromCookie();
}
