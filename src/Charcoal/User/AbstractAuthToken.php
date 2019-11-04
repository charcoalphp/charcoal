<?php

namespace Charcoal\User;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From 'charcoal-object'
use Charcoal\Object\TimestampableInterface;
use Charcoal\Object\TimestampableTrait;

// From 'charcoal-user'
use Charcoal\User\AuthTokenMetadata;

/**
 * Base Authorization Token
 */
abstract class AbstractAuthToken extends AbstractModel implements
    AuthTokenInterface,
    TimestampableInterface
{
    use TimestampableTrait;

    /**
     * The token key.
     *
     * @var string
     */
    private $ident;

    /**
     * The token value.
     *
     * @var string
     */
    private $token;

    /**
     * The related user ID.
     *
     * @var string
     */
    private $userId;

    /**
     * The token's expiration date.
     *
     * @var DateTimeInterface|null
     */
    private $expiry;

    /**
     * @return string
     */
    public function key()
    {
        return 'ident';
    }

    /**
     * @param  string $ident The token ident.
     * @return self
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param  string $token The token.
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param  string $id The user ID.
     * @throws InvalidArgumentException If the user ID is not a string.
     * @return self
     */
    public function setUserId($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                'Set User ID: identifier must be a string'
            );
        }

        $this->userId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param  DateTimeInterface|string|null $expiry The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return self
     */
    public function setExpiry($expiry)
    {
        if ($expiry === null) {
            $this->expiry = null;
            return $this;
        }

        if (is_string($expiry)) {
            $expiry = new DateTime($expiry);
        }

        if (!($expiry instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Expiry" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Generate auth token data for the given user ID.
     *
     * Note: the `random_bytes()` function is new to PHP-7. Available in PHP 5 with `compat-random`.
     *
     * @param  string $userId The user ID to generate the auth token from.
     * @return self
     */
    public function generate($userId)
    {
        if (!$this->isEnabled()) {
            return $this;
        }

        $metadata = $this->metadata();

        $this['ident']  = bin2hex(random_bytes(16));
        $this['token']  = bin2hex(random_bytes(32));
        $this['userId'] = $userId;
        $this['expiry'] = 'now + '.$metadata['tokenDuration'];

        return $this;
    }

    /**
     * Determine if authentication by token is supported.
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->metadata()['enabled'];
    }

    /**
     * Determine if authentication by token should be only over HTTPS.
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->metadata()['httpsOnly'];
    }

    /**
     * @param  mixed  $ident The auth-token identifier.
     * @param  string $token The token to validate against.
     * @return mixed The user id. An empty string if no token match.
     */
    public function getUserIdFromToken($ident, $token)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if (!$this->source()->tableExists()) {
            $this->logger->warning(sprintf(
                '[AuthToken] Invalid login attempt from token "%s": The table "%s" does not exist.',
                $ident,
                $this->source()->table()
            ));
            return null;
        }

        $this->load($ident);
        if (!$this['ident']) {
            $this->logger->warning(sprintf(
                '[AuthToken] Token not found: "%s"',
                $ident
            ));
            return null;
        }

        // Expired token
        $now = new DateTime('now');
        if (!$this['expiry'] || $now > $this['expiry']) {
            $this->logger->warning(
                '[AuthToken] Token expired',
                $ident
            );
            $this->delete();
            return null;
        }

        // Validate encrypted token
        if (password_verify($token, $this['token']) !== true) {
            $this->panic();
            $this->delete();
            return null;
        }

        // Success!
        return $this['userId'];
    }

    /**
     * Delete all auth tokens from storage for the current user.
     *
     * @return void
     */
    public function deleteUserAuthTokens()
    {
        $userId = $this['userId'];
        if (isset($userId)) {
            $source = $this->source();

            if (!$source->tableExists()) {
                return;
            }

            $sql = sprintf(
                'DELETE FROM `%s` WHERE user_id = :userId',
                $source->table()
            );
            $source->dbQuery($sql, [
                'userId' => $userId,
            ]);
        }
    }

    /**
     * Something is seriously wrong: a auth ident was in the database but with a tampered token.
     *
     * @return void
     */
    protected function panic()
    {
        $this->logger->error(
            '[AuthToken] Possible security breach: an authentication token was found in the database but its token does not match.'
        );

        $this->deleteUserAuthTokens();
    }

    /**
     * @see \Charcoal\Source\StorableTrait::preSave()
     *
     * @return boolean
     */
    protected function preSave()
    {
        $result = parent::preSave();

        $this->touchToken();

        $this['created']      = 'now';
        $this['lastModified'] = 'now';

        return $result;
    }

    /**
     * @see \Charcoal\Source\StorableTrait::preUpdate()
     *
     * @param  array $properties The properties (ident) set for update.
     * @return boolean
     */
    protected function preUpdate(array $properties = null)
    {
        $result = parent::preUpdate($properties);

        $this['lastModified'] = 'now';

        return $result;
    }

    /**
     * @return void
     */
    protected function touchToken()
    {
        $token = $this['token'];
        if (password_needs_rehash($token, PASSWORD_DEFAULT)) {
            $this['token'] = password_hash($token, PASSWORD_DEFAULT);
        }
    }

    /**
     * Create a new metadata object.
     *
     * @param  array $data Optional metadata to merge on the object.
     * @return AuthTokenMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $class = $this->metadataClass();
        return new $class($data);
    }

    /**
     * Retrieve the class name of the metadata object.
     *
     * @return string
     */
    protected function metadataClass()
    {
        return AuthTokenMetadata::class;
    }
}
