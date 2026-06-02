<?php

declare(strict_types=1);

namespace Charcoal\User;

/**
 * Authorization Token
 *
 * To keep a user logged in using a cookie and a database token.
 */
class AuthToken extends AbstractAuthToken implements
    AuthTokenCookieInterface
{
    use AuthTokenCookieTrait;
}
