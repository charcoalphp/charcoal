<?php

namespace Charcoal\User;

/**
 *
 */
interface AuthTokenInterface
{
    /**
     * @param string $ident The token ident.
     * @return self
     */
    public function setIdent($ident);

    /**
     * @return string
     */
    public function getIdent();

    /**
     * @param  string $token The token.
     * @return self
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param  string $id The user ID.
     * @throws InvalidArgumentException If the user ID is not a string.
     * @return self
     */
    public function setUserId($id);

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param  DateTimeInterface|string|null $expiry The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setExpiry($expiry);

    /**
     * @return DateTimeInterface|null
     */
    public function getExpiry();

    /**
     * @param  DateTimeInterface|string|null $created The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setCreated($created);

    /**
     * @return DateTimeInterface|null
     */
    public function getCreated();

    /**
     * @param  DateTimeInterface|string|null $lastModified The last modified date/time.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setLastModified($lastModified);

    /**
     * @return DateTimeInterface|null
     */
    public function getLastModified();

    /**
     * Generate auth token data for the given user ID.
     *
     * @param  string $userId The user ID to generate the auth token from.
     * @return self
     */
    public function generate($userId);

    /**
     * Determine if authentication by token is supported.
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Determine if authentication by token should be only over HTTPS.
     *
     * @return boolean
     */
    public function isSecure();

    /**
     * @param  mixed  $ident The auth-token identifier.
     * @param  string $token The token to validate against.
     * @return mixed The user id. An empty string if no token match.
     */
    public function getUserIdFromToken($ident, $token);

    /**
     * Delete all auth tokens from storage for the current user.
     *
     * @return void
     */
    public function deleteUserAuthTokens();
}
