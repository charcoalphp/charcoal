<?php

declare(strict_types=1);

namespace Charcoal\Email\Objects;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
// From 'charcoal/core'
use Charcoal\Model\AbstractModel;
use Charcoal\Email\EmailAwareTrait;

/**
 * Email log
 */
class EmailLog extends AbstractModel
{
    use EmailAwareTrait;

    /**
     * The Queue ID
     *
     * @var string $queueId
     */
    private $queueId;

    /**
     * The error code
     *
     * @var string $errorCode
     */
    private $errorCode;

    /**
     * The Message-ID (Unique message identifier)
     */
    private ?string $messageId = null;

    /**
     * The campaign ID.
     *
     * @var string $campaign
     */
    private $campaign;

    /**
     * The sender's email address.
     */
    private ?string $from = null;

    /**
     * The recipient's email address.
     */
    private ?string $to = null;

    /**
     * The email subject.
     */
    private ?string $subject = null;

    /**
     * When the email should be sent.
     */
    private ?\DateTimeInterface $sendTs = null;

    /**
     * Get the primary key that uniquely identifies each queue item.
     */
    #[\Override]
    public function key(): string
    {
        return 'id';
    }

    /**
     * Set the queue ID.
     *
     * @param  string $queueId The queue ID.
     */
    public function setQueueId($queueId): static
    {
        $this->queueId = $queueId;

        return $this;
    }

    /**
     * Get the queue ID.
     *
     * @return string
     */
    public function queueId()
    {
        return $this->queueId;
    }

    /**
     * Set the error code.
     *
     * @param  string $errorCode The error code.
     */
    public function setErrorCode($errorCode): static
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function errorCode()
    {
        return $this->errorCode;
    }

    /**
     * Set the Message-ID.
     *
     * @param string $messageId The Message-ID.
     * @throws InvalidArgumentException If the Message-ID is not a string.
     */
    public function setMessageId($messageId): static
    {
        if (!is_string($messageId)) {
            throw new InvalidArgumentException(
                'Message-ID must be a string.'
            );
        }

        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Get the Message-ID.
     *
     * @return string
     */
    public function messageId(): ?string
    {
        return $this->messageId;
    }

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign is invalid.
     */
    public function setCampaign($campaign): static
    {
        if ($campaign !== null && !is_string($campaign)) {
            throw new InvalidArgumentException(
                'Campaign must be a string'
            );
        }

        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get the campaign identifier.
     *
     * @return string
     */
    public function campaign()
    {
        return $this->campaign;
    }

    /**
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function setFrom($email): static
    {
        $this->from = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the sender's email address.
     *
     * @return string
     */
    public function from(): ?string
    {
        return $this->from;
    }

    /**
     * Set the recipient's email address.
     *
     * @param  string|array $email An email address.
     */
    public function setTo($email): static
    {
        $this->to = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the recipient's email address.
     *
     * @return string
     */
    public function to(): ?string
    {
        return $this->to;
    }

    /**
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     * @throws InvalidArgumentException If the subject is not a string.
     */
    public function setSubject($subject): static
    {
        if (!is_string($subject)) {
            throw new InvalidArgumentException(
                'Subject needs to be a string'
            );
        }

        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the email subject.
     *
     * @return string
     */
    public function subject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param  null|string|DateTime $ts The "send date" datetime value.
     * @throws InvalidArgumentException If the ts is not a valid datetime value.
     */
    public function setSendTs($ts): static
    {
        if ($ts === null) {
            $this->sendTs = null;
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

        $this->sendTs = $ts;
        return $this;
    }

    public function sendTs(): ?\DateTimeInterface
    {
        return $this->sendTs;
    }

    /**
     * @see    StorableTrait::preSave()
     */
    #[\Override]
    protected function preSave(): bool
    {
        parent::preSave();

        if (!$this->sendTs() instanceof \DateTimeInterface) {
            $this->setSendTs('now');
        }

        return true;
    }
}
