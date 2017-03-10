<?php

namespace Charcoal\User;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

// From 'charcoal-user'
use Charcoal\User\AuthTokenMetadata;

/**
 * Authorization token; to keep a user logged in
 */
class AuthToken extends AbstractModel
{
    /**
     * @var string
     */
    private $ident;

    /**
     * @var string
     */
    private $token;

    /**
     * The username should be unique and mandatory.
     * @var string
     */
    private $username;

    /**
     * @var DateTimeInterface|null
     */
    private $expiry;

    /**
     * Token creation date (set automatically on save)
     * @var DateTimeInterface|null
     */
    private $created;

    /**
     * Token last modified date (set automatically on save and update)
     * @var DateTimeInterface|null
     */
    private $lastModified;

    /**
     * @return string
     */
    public function key()
    {
        return 'ident';
    }

    /**
     * @param string $ident The token ident.
     * @return AuthToken Chainable
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
        return $this;
    }

    /**
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * @param string $token The token.
     * @return AuthToken Chainable
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function token()
    {
        return $this->token;
    }


    /**
     * Force a lowercase username
     *
     * @param string $username The username (also the login name).
     * @throws InvalidArgumentException If the username is not a string.
     * @return AuthToken Chainable
     */
    public function setUsername($username)
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException(
                'Set user username: Username must be a string'
            );
        }
        $this->username = mb_strtolower($username);
        return $this;
    }

    /**
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * @param DateTimeInterface|string|null $expiry The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return AuthToken Chainable
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
    public function expiry()
    {
        return $this->expiry;
    }

    /**
     * @param DateTimeInterface|string|null $created The date/time at object's creation.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return AuthToken Chainable
     */
    public function setCreated($created)
    {
        if ($created === null) {
            $this->created = null;
            return $this;
        }
        if (is_string($created)) {
            $created = new DateTime($created);
        }
        if (!($created instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Created" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->created = $created;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function created()
    {
        return $this->created;
    }

    /**
     * @param DateTimeInterface|string|null $lastModified The last modified date/time.
     * @throws InvalidArgumentException If the date/time is invalid.
     * @return AuthToken Chainable
     */
    public function setLastModified($lastModified)
    {
        if ($lastModified === null) {
            $this->lastModified = null;
            return $this;
        }
        if (is_string($lastModified)) {
            $lastModified = new DateTime($lastModified);
        }
        if (!($lastModified instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Last Modified" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function lastModified()
    {
        return $this->lastModified;
    }

    /**
     * Note: the `random_bytes()` function is new to PHP-7. Available in PHP 5 with `compat-random`.
     *
     * @param string $username The username to generate the auth token from.
     * @return AuthToken Chainable
     */
    public function generate($username)
    {
        $this->setIdent(bin2hex(random_bytes(16)));
        $this->setToken(bin2hex(random_bytes(32)));
        $this->setUsername($username);
        $this->setExpiry('now + '.$this->metadata()->cookieDuration());

        return $this;
    }

    /**
     * @return AuthToken Chainable
     */
    public function sendCookie()
    {
        $cookieName = $this->metadata()->cookieName();
        $value = $this->ident().';'.$this->token();
        $expiry = $this->expiry()->getTimestamp();
        $secure = $this->metadata()->httpsOnly();

        setcookie($cookieName, $value, $expiry, '', '', $secure);

        return $this;
    }

     /**
      * StorableTrait > preSave(): Called automatically before saving the object to source.
      * @return boolean
      */
    public function preSave()
    {
        parent::preSave();

        if (password_needs_rehash($this->token, PASSWORD_DEFAULT)) {
            $this->token = password_hash($this->token, PASSWORD_DEFAULT);
        }
        $this->setCreated('now');
        $this->setLastModified('now');

        return true;
    }

    /**
     * StorableTrait > preUpdate(): Called automatically before updating the object to source.
     * @param array $properties The properties (ident) set for update.
     * @return boolean
     */
    public function preUpdate(array $properties = null)
    {
        parent::preUpdate($properties);

        $this->setLastModified('now');

        return true;
    }

    /**
     * @return array|null `['ident'=>'', 'token'=>'']
     */
    public function getTokenDataFromCookie()
    {
        $cookieName = $this->metadata()->cookieName();

        if (!isset($_COOKIE[$cookieName])) {
            return null;
        }

        $authCookie = $_COOKIE[$cookieName];
        $vals = explode(';', $authCookie);
        if (!isset($vals[0]) || !isset($vals[1])) {
            return null;
        }

        return [
            'ident' => $vals[0],
            'token' => $vals[1]
        ];
    }

    /**
     * @param mixed  $ident The auth-token identifier.
     * @param string $token The token key to validate against.
     * @return mixed The user id.
     */
    public function getUserId($ident, $token)
    {
        return $this->getUsernameFromToken($ident, $token);
    }

    /**
     * @param mixed  $ident The auth-token identifier (username).
     * @param string $token The token to validate against.
     * @return mixed The user id. An empty string if no token match.
     */
    public function getUsernameFromToken($ident, $token)
    {
        if (!$this->source()->tableExists()) {
            return '';
        }

        $this->load($ident);
        if (!$this->ident()) {
            $this->logger->warning(sprintf(
                'Auth token not found: "%s"',
                $ident
            ));
            return '';
        }

        // Expired cookie
        $now = new DateTime('now');
        if (!$this->expiry() || $now > $this->expiry()) {
            $this->logger->warning('Expired auth token');
            $this->delete();
            return '';
        }

        // Validate encrypted token
        if (password_verify($token, $this->token()) !== true) {
            $this->panic();
            $this->delete();
            return '';
        }

        // Success!
        return $this->username();
    }

    /**
     * Something is seriously wrong: a cookie ident was in the database but with a tampered token.
     *
     * @return void
     */
    protected function panic()
    {
        $this->logger->error(
            'Possible security breach: an authentication token was found in the database but its token does not match.'
        );

        if ($this->username) {
            $table = $this->source()->table();
            $q = sprintf('
                DELETE FROM
                    `%s`
                WHERE
                    username = :username', $table);
            $this->source()->dbQuery($q, [
                'username' => $this->username()
            ]);
        }
    }

    /**
     * DescribableTrait > create_metadata().
     *
     * @param array $data Optional data to intialize the Metadata object with.
     * @return AuthTokenMetadata
     */
    protected function createMetadata(array $data = null)
    {
        $metadata = new AuthTokenMetadata();
        if ($data !== null) {
            $metadata->setData($data);
        }
        return $metadata;
    }
}
