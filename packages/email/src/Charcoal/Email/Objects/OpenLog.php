<?php

declare(strict_types=1);

namespace Charcoal\Email\Objects;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
// From 'charcoal/core'
use Charcoal\Model\AbstractModel;

/**
 * Open log
 */
class OpenLog extends AbstractModel
{
    private ?string $email = null;

    private ?\DateTimeInterface $ts = null;

    private ?string $ip = null;


    /**
     * @param string|null $emailId The email (log) id.
     */
    public function setEmail(?string $emailId): static
    {
        $this->email = $emailId;
        return $this;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @param  null|string|DateTimeInterface $ts The "timestamp" datetime value.
     * @throws InvalidArgumentException If the timestamp is not a valid datetime value.
     */
    public function setTs($ts): static
    {
        if ($ts === null) {
            $this->ts = null;
            return $this;
        }

        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }
        }

        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Send Date" value. Must be a date/time string or a DateTime object.'
            );
        }

        $this->ts = $ts;
        return $this;
    }

    public function ts(): ?\DateTimeInterface
    {
        return $this->ts;
    }

    /**
     * @param string|null $ip The IP address.
     */
    public function setIp(?string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function ip(): ?string
    {
        return $this->ip;
    }
}
