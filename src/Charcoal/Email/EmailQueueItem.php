<?php

namespace Charcoal\Email;

use \Exception;
use \InvalidArgumentException;

// Module `pimple/pimple` dependencies
use \Pimple\Container;

// Module `charcoal/factory` dependencies
use \Charcoal\Factory\FactoryInterface;

// Module `charcoal-core` dependencies
use \Charcoal\Model\AbstractModel;

// Module `charcoal-queue` dependencies
use \Charcoal\Queue\QueueItemInterface;
use \Charcoal\Queue\QueueItemTrait;

// Module `charcoal-app` dependencies
use \Charcoal\App\App;
use \Charcoal\Email\Email;

/**
 * Email queue item.
 */
class EmailQueueItem extends AbstractModel implements QueueItemInterface
{
    use QueueItemTrait;
    use EmailAwareTrait;

    /**
     * The queue item ID.
     *
     * @var string $ident
     */
    private $ident;

    /**
     * The recipient's email address.
     *
     * @var string $to
     */
    private $to;

    /**
     * The sender's email address.
     *
     * @var string $from
     */
    private $from;

    /**
     * The email subject.
     *
     * @var string $subject.
     */
    private $subject;

    /**
     * The HTML message body.
     *
     * @var string $msgHtml
     */
    private $msgHtml;

    /**
     * The plain-text message body.
     *
     * @var string $msgTxt
     */
    private $msgTxt;

    /**
     * The campaign ID.
     *
     * @var string $campaign
     */
    private $campaign;

    /**
     * @var FactoryInterface $emailFactory
     */
    private $emailFactory;

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->setEmailFactory($container['email/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory to create email objects.
     * @return EmailQueueItem Chainable
     */
    protected function setEmailFactory(FactoryInterface $factory)
    {
        $this->emailFactory = $factory;
        return $this;
    }

    /**
     * @return FactoryInterface
     */
    protected function emailFactory()
    {
        return $this->emailFactory;
    }

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
     * Set the queue item's ID.
     *
     * @param  string|null $ident The unique queue item identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     * @return AbstractMessage Chainable
     */
    public function setIdent($ident)
    {
        if ($ident === null) {
            $this->ident = null;
            return $this;
        }

        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Ident needs to be a string'
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Get the queue item's ID.
     *
     * @return string
     */
    public function ident()
    {
        return $this->ident;
    }

    /**
     * Set the recipient's email address.
     *
     * @param  string|array $email An email address.
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailQueueItem Chainable
     */
    public function setTo($email)
    {
        if (is_string($email)) {
            $this->to = $email;
        } elseif (is_array($email)) {
            $this->to = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Queue item recipient: Email address must to be a string.'
            );
        }

        $this->to = $email;

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
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailQueueItem Chainable
     */
    public function setFrom($email)
    {
        if (is_string($email)) {
            $this->from = $email;
        } elseif (is_array($email)) {
            $this->from = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Queue item sender: Email address must to be a string.'
            );
        }

        $this->from = $email;

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
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     * @throws InvalidArgumentException If the subject is not a string.
     * @return EmailQueueItem Chainable
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
     * Set the email's HTML message body.
     *
     * @param  string $body The HTML message body.
     * @throws InvalidArgumentException If the message is not a string.
     * @return EmailQueueItem Chainable
     */
    public function setMsgHtml($body)
    {
        if (!is_string($body)) {
            throw new InvalidArgumentException(
                'HTML message needs to be a string'
            );
        }

        $this->msgHtml = $body;

        return $this;
    }

    /**
     * Get the email's HTML message body.
     *
     * @return string
     */
    public function msgHtml()
    {
        return $this->msgHtml;
    }

    /**
     * Set the email's plain-text message body.
     *
     * @param  string $body The plain-text mesage body.
     * @throws InvalidArgumentException If the message is not a string.
     * @return EmailQueueItem Chainable
     */
    public function setMsgTxt($body)
    {
        if (!is_string($body)) {
            throw new InvalidArgumentException(
                'Plan-text message needs to be a string'
            );
        }

        $this->msgTxt = $body;

        return $this;
    }

    /**
     * Get the email's plain-text message body.
     *
     * @return string
     */
    public function msgTxt()
    {
        return $this->msgTxt;
    }

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign is not a string.
     * @return EmailQueueItem Chainable
     */
    public function setCampaign($campaign)
    {
        if (!is_string($campaign)) {
            throw new InvalidArgumentException(
                'Campaign ID needs to be a string'
            );
        }

        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get the campaign ID.
     *
     * If it has not been explicitely set, it will be auto-generated (with uniqid).
     *
     * @return string
     */
    public function campaign()
    {
        return $this->campaign;
    }

    /**
     * Process the item.
     *
     * @param  callable $callback        An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean  Success / Failure
     */
    public function process(
        callable $callback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    ) {
        if ($this->processed() === true) {
            // Do not process twice, ever.
            return null;
        }

        $email = $this->emailFactory()->create('email');

        $email->setData($this->data());

        try {
            $res = $email->send();
            if ($res === true) {
                $this->setProcessed(true);
                $this->setProcessedDate('now');
                $this->update();

                if ($successCallback !== null) {
                    $successCallback($this);
                }
            } else {
                if ($failureCallback !== null) {
                    $failureCallback($this);
                }
            }

            if ($callback !== null) {
                $callback($this);
            }

            return $res;
        } catch (Exception $e) {
            // Todo log error
            if ($failureCallback !== null) {
                $failureCallback($this);
            }

            return false;
        }
    }

    /**
     * Hook called before saving the item.
     *
     * @return boolean
     * @see \Charcoal\Email\Queue\QueueItemTrait::preSaveQueueItem()
     */
    public function preSave()
    {
        parent::preSave();

        $this->preSaveQueueItem();

        return true;
    }
}
