<?php

declare(strict_types=1);

namespace Charcoal\Email\Objects;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From 'locomotivemtl/charcoal-core'
use Charcoal\Model\AbstractModel;

/**
 * Open log
 */
class OpenLog extends AbstractModel
{
    /**
     * @var string|null
     */
    private $email;

    /**
     * @var DateTimeInterface|null
     */
    private $ts;

    /**
     * @var string|null
     */
    private $ip;


    /**
     * @param string|null $emailId The email (log) id.
     * @return self
     */
    public function setEmail(?string $emailId)
    {
        $this->email = $emailId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->email;
    }

    /**
     * @param  null|string|DateTimeInterface $ts The "timestamp" datetime value.
     * @throws InvalidArgumentException If the timestamp is not a valid datetime value.
     * @return self
     */
    public function setTs($ts)
    {
        if ($ts === null) {
            $this->ts = null;
            return $this;
        }

        if (is_string($ts)) {
            try {
                $ts = new DateTime($ts);
            } catch (Exception $e) {
                throw new InvalidArgumentException($e->getMessage());
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

    /**
     * @return null|DateTimeInterface
     */
    public function ts()
    {
        return $this->ts;
    }

    /**
     * @param string|null $ip The IP address.
     * @return self
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return string|null
     */
    public function ip(): ?string
    {
        return $this->ip;
    }
}
