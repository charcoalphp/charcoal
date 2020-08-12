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
 * Link (click) log
 */
class LinkLog extends AbstractModel
{
    /**
     * @var string|null
     */
    private $link;

    /**
     * @var DateTimeInterface|null
     */
    private $ts;

    /**
     * @var string|null
     */
    private $ip;

    /**
     * @param string|null $linkId The link id.
     * @return self
     */
    public function setLink(?string $linkId)
    {
        $this->link = $linkId;
        return $this;
    }

    /**
     * @return string
     */
    public function link(): ?string
    {
        return $this->link;
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
