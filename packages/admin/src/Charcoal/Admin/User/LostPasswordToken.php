<?php

namespace Charcoal\Admin\User;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * Time-limited password reset token.
 *
 * A public token is emailed as `{ident}{plainToken}`. Only a password hash of
 * the secret is persisted; lookup uses the public ident.
 */
class LostPasswordToken extends AbstractModel
{
    /**
     * Byte length of the public ident (hex-encoded to twice as many characters).
     *
     * @var integer
     */
    public const IDENT_BYTES = 16;

    /**
     * Byte length of the secret token (hex-encoded to twice as many characters).
     *
     * @var integer
     */
    public const TOKEN_BYTES = 32;

    /**
     * @var string|null
     */
    private $ident;

    /**
     * @var string|null
     */
    private $token;

    /**
     * Plain-text secret, available only between {@see generate()} and persistence.
     *
     * @var string|null
     */
    private $plainToken;

    /**
     * @var mixed
     */
    private $user;

    /**
     * @var DateTimeInterface|null
     */
    private $expiry;

    /**
     * @var mixed
     */
    private $defaultExpiry = '30 minutes';

    /**
     * @return string
     */
    public function key()
    {
        return 'ident';
    }

    /**
     * Generate a public ident and a secret token for the given user.
     *
     * The secret is stored in plain text until {@see preSave()} hashes it.
     *
     * @param  string $userId The user ID to generate the token for.
     * @return self
     */
    public function generate($userId)
    {
        $this->ident      = bin2hex(random_bytes(self::IDENT_BYTES));
        $this->plainToken = bin2hex(random_bytes(self::TOKEN_BYTES));
        $this->token      = $this->plainToken;
        $this->user       = $userId;

        return $this;
    }

    /**
     * Public token sent by email / submitted by the user.
     *
     * @return string|null
     */
    public function publicToken()
    {
        if ($this->ident === null || $this->plainToken === null) {
            return null;
        }

        return $this->ident . $this->plainToken;
    }

    /**
     * Split a public token into ident (lookup) and secret (verification).
     *
     * @param  string $publicToken The public token from the URL or form.
     * @return array|null
     */
    public function parsePublicToken($publicToken)
    {
        $identLen = (self::IDENT_BYTES * 2);
        $tokenLen = (self::TOKEN_BYTES * 2);

        if (!is_string($publicToken) || strlen($publicToken) !== ($identLen + $tokenLen)) {
            return null;
        }

        return [
            'ident' => substr($publicToken, 0, $identLen),
            'token' => substr($publicToken, $identLen),
        ];
    }

    /**
     * Load and validate a public reset token.
     *
     * To be valid, a token should:
     *
     * - match an ident in the database
     * - not be expired
     * - optionally match the given user
     * - verify against the stored password hash
     *
     * @param  string      $publicToken The public token to validate.
     * @param  string|null $userId      Optional user ID that should match the token.
     * @return boolean
     */
    public function loadFromPublicToken($publicToken, $userId = null)
    {
        $parsed = $this->parsePublicToken($publicToken);
        if ($parsed === null) {
            return false;
        }

        $this->load($parsed['ident']);
        if (!$this->ident()) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($userId !== null && (string)$this->user() !== (string)$userId) {
            return false;
        }

        if (password_verify($parsed['token'], $this->token()) !== true) {
            return false;
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        if ($this->expiry === null) {
            return true;
        }

        return (new DateTime('now')) > $this->expiry;
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
    public function ident()
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
    public function token()
    {
        return $this->token;
    }

    /**
     * @return string|null
     */
    public function plainToken()
    {
        return $this->plainToken;
    }

    /**
     * @param  string $user The user.
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function user()
    {
        return $this->user;
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
            try {
                $expiry = new DateTime($expiry);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
            }
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
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->defaultExpiry = ($container['admin/config']['login']['token_expiry'] ?? '2 hours');
    }

    /**
     * @see    \Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    protected function preSave()
    {
        if ($this->expiry === null) {
            $this->setExpiry('now +' . $this->defaultExpiry);
        }

        $this->hashToken();

        return parent::preSave();
    }

    /**
     * Password-hash the secret token before persistence.
     *
     * @return void
     */
    protected function hashToken()
    {
        $token = $this->token;
        if ($token && password_needs_rehash($token, PASSWORD_DEFAULT)) {
            $this->token = password_hash($token, PASSWORD_DEFAULT);
        }
    }
}
