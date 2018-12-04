<?php

namespace Charcoal\Email;

// Dependencies from `PHP`
use DateTime;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

// From `charcoal-core`
use Charcoal\Model\AbstractModel;

/**
 * Email log
 */
class EmailLog extends AbstractModel
{
    use EmailAwareTrait;

    /**
     * Type of log (e.g., "email").
     *
     * @var string $type
     */
    private $type;

    /**
     * The action logged (e.g., "send").
     *
     * @var string $action
     */
    private $action;

    /**
     * The mailer's raw response.
     *
     * @var mixed $rawResponse
     */
    private $rawResponse;

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
     * Whether the email has been semt.
     *
     * Error code (0 = success)
     *
     * @var int $sendStatus
     */
    private $sendStatus;

    /**
     * The error message from a failed send.
     *
     * @var string $sendError
     */
    private $sendError;

    /**
     * When the email should be sent.
     *
     * @var DateTimeInterface|null $sendDate
     */
    private $sendDate;

    /**
     * The current IP address at the time of the log.
     *
     * @var string $ip
     */
    private $ip;

    /**
     * The current session ID at the time of the log.
     *
     * @var string $sessionId
     */
    private $sessionId;

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
     * Set the type of log.
     *
     * @param  string $type The log type. (e.g., "email").
     * @throws InvalidArgumentException If the log type is not a string.
     * @return self
     */
    public function setType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(
                'Log type must be a string.'
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get the log type.
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Set the logged action.
     *
     * @param  string $action The log action (e.g., "send").
     * @throws InvalidArgumentException If the action is not a string.
     * @return self
     */
    public function setAction($action)
    {
        if (!is_string($action)) {
            throw new InvalidArgumentException(
                'Action must be a string.'
            );
        }

        $this->action = $action;

        return $this;
    }

    /**
     * Get the logged action.
     *
     * @return string
     */
    public function action()
    {
        return $this->action;
    }

    /**
     * Set the raw response from the mailer.
     *
     * @param mixed $res The response object or array.
     * @return self
     */
    public function setRawResponse($res)
    {
        $this->rawResponse = $res;
        return $this;
    }

    /**
     * Get the raw response from the mailer.
     *
     * @return mixed
     */
    public function rawResponse()
    {
        return $this->rawResponse;
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
    public function setSendDate($ts)
    {
        if ($ts === null) {
            $this->sendDate = null;
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

        $this->sendDate = $ts;
        return $this;
    }

    /**
     * @return null|DateTimeInterface
     */
    public function sendDate()
    {
        return $this->sendDate;
    }

    /**
     * @param mixed $ip The IP adress.
     * @return self
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function ip()
    {
        return $this->ip;
    }

    /**
     * @param string $sessionId The session identifier.
     * @return self
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return string
     */
    public function sessionId()
    {
        return $this->sessionId;
    }

    /**
     * @see    StorableTrait::preSave()
     * @return boolean
     */
    protected function preSave()
    {
        parent::preSave();

        $ip = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
        $sessionId = session_id();

        $this->setIp($ip);
        $this->setSessionId($sessionId);

        if ($this->sendDate() === null) {
            $this->setSendDate('now');
        }

        return true;
    }
}
