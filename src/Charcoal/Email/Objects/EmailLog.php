<?php

namespace Charcoal\Email\Objects;

use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From 'locomotivemtl/charcoal-core'
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
     *
     * @var string $messageId
     */
    private $messageId;

    /**
     * The campaign ID.
     *
     * @var string $campaign
     */
    private $campaign;

    /**
     * The sender's email address.
     *
     * @var string $from
     */
    private $from;

    /**
     * The recipient's email address.
     *
     * @var string $to
     */
    private $to;

    /**
     * The email subject.
     *
     * @var string $subject
     */
    private $subject;

    /**
     * When the email should be sent.
     *
     * @var DateTimeInterface|null $sendTs
     */
    private $sendTs;

    /**
     * Get the primary key that uniquely identifies each queue item.
     *
     * @return string
     */
    public function key()
    {
        return 'id';
    }

    /**
     * Set the queue ID.
     *
     * @param  string $queueId The queue ID.
     * @return self
     */
    public function setQueueId($queueId)
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
     * @return self
     */
    public function setErrorCode($errorCode)
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
     * @return self
     */
    public function setMessageId($messageId)
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
    public function messageId()
    {
        return $this->messageId;
    }

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign is invalid.
     * @return self
     */
    public function setCampaign($campaign)
    {
        if (!is_string($campaign)) {
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
     * @return self
     */
    public function setFrom($email)
    {
        $this->from = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the sender's email address.
     *
     * @return string
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * Set the recipient's email address.
     *
     * @param  string|array $email An email address.
     * @return self
     */
    public function setTo($email)
    {
        $this->to = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the recipient's email address.
     *
     * @return string
     */
    public function to()
    {
        return $this->to;
    }

    /**
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     * @throws InvalidArgumentException If the subject is not a string.
     * @return self
     */
    public function setSubject($subject)
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
    public function subject()
    {
        return $this->subject;
    }

    /**
     * @param  null|string|DateTime $ts The "send date" datetime value.
     * @throws InvalidArgumentException If the ts is not a valid datetime value.
     * @return self
     */
    public function setSendTs($ts)
    {
        if ($ts === null) {
            $this->sendTs = null;
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

        $this->sendTs = $ts;
        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function sendTs()
    {
        return $this->sendTs;
    }

    /**
     * @see    StorableTrait::preSave()
     * @return boolean
     */
    protected function preSave() : bool
    {
        parent::preSave();

        if ($this->sendTs() === null) {
            $this->setSendTs('now');
        }

        return true;
    }
}
