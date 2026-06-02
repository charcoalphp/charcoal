<?php

declare(strict_types=1);

namespace Charcoal\Admin\User;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 *
 */
class LostPasswordToken extends AbstractModel
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var mixed
     */
    private $user;

    private ?\DateTimeInterface $expiry = null;

    /**
     * @var mixed
     */
    private $defaultExpiry = '30 minutes';

    #[\Override]
    public function key(): string
    {
        return 'token';
    }

    /**
     * @param  string $token The token.
     */
    public function setToken($token): static
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
     * @param  string $user The user.
     */
    public function setUser($user): static
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
     */
    public function setExpiry($expiry): static
    {
        if ($expiry === null) {
            $this->expiry = null;
            return $this;
        }

        if (is_string($expiry)) {
            try {
                $expiry = new DateTime($expiry);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
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

    public function expiry(): ?\DateTimeInterface
    {
        return $this->expiry;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->defaultExpiry = ($container['admin/config']['login']['token_expiry'] ?? '2 hours');
    }

    /**
     * @see    \Charcoal\Source\StorableTrait::preSave() For the "create" Event.
     * @return boolean
     */
    #[\Override]
    protected function preSave()
    {
        if (!$this->expiry instanceof \DateTimeInterface) {
            $this->setExpiry('now +' . $this->defaultExpiry);
        }

        return parent::preSave();
    }
}
