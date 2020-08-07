<?php

declare(strict_types=1);

namespace Charcoal\Email;

use Exception;
use InvalidArgumentException;

// From 'pimple/pimple'
use Pimple\Container;

// From 'locomotivemtl/charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'locomotivemtl/charcoal-core'
use Charcoal\Model\AbstractModel;

// From 'locomotivemtl/charcoal-queue'
use Charcoal\Queue\QueueItemInterface;
use Charcoal\Queue\QueueItemTrait;

/**
 * Email queue item.
 */
class EmailQueueItem extends AbstractModel implements QueueItemInterface
{
    use QueueItemTrait;
    use EmailAwareTrait;

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
     * Get the primary key that uniquely identifies each queue item.
     *
     * @return string
     */
    public function key()
    {
        return 'id';
    }

    /**
     * Set the recipient's email address.
     *
     * @param  string|array $email An email address.
     * @return self
     */
    public function setTo($email)
    {
        try {
            $this->to = $this->parseEmail($email);
        } catch (Exception $e) {
            $this->logger->warning(sprintf('Invalid "to" email: "%s"', strval($email)));
        }
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
     * @return self
     */
    public function setFrom($email)
    {
        try {
            $this->from = $this->parseEmail($email);
        } catch (Exception $e) {
            $this->logger->warning(sprintf('Invalid "from" email: "%s"', strval($email)));
        }
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
     * @return self
     */
    public function setSubject($subject)
    {
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
     * @return self
     */
    public function setMsgHtml($body)
    {
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
     * @return self
     */
    public function setMsgTxt($body)
    {
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
     * @return self
     */
    public function setCampaign($campaign)
    {
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
     * @param  callable $alwaysCallback  An optional callback routine executed after the item is processed.
     * @param  callable $successCallback An optional callback routine executed when the item is resolved.
     * @param  callable $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean|null Returns TRUE i this item was successfully processed,
     *     FALSE on failure or if an error occurs, NULL if this item is already processed.
     */
    public function process(
        callable $alwaysCallback = null,
        callable $successCallback = null,
        callable $failureCallback = null
    ): ?bool {
        if ($this->processed() === true) {
            // Do not process twice, ever.
            return null;
        }

        $email = $this->emailFactory()->create('email');
        $email->setData($this->data());

        try {
            $result = $email->send();
            if ($result === true) {
                $this->setProcessed(true);
                $this->setProcessedDate('now');
                $this->setMsgHtml(null);
                $this->setMsgTxt(null);
                $this->update([
                    'processed',
                    'processed_date',
                    'msg_html',
                    'msg_txt',
                ]);

                if ($successCallback !== null) {
                    $successCallback($this);
                }
            } else {
                if ($failureCallback !== null) {
                    $failureCallback($this);
                }
            }

            return $result;
        } catch (Exception $e) {
            // Todo log error
            if ($failureCallback !== null) {
                $failureCallback($this);
            }

            return false;
        } finally {
            if ($alwaysCallback !== null) {
                $alwaysCallback($this);
            }
        }
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container): void
    {
        parent::setDependencies($container);
        $this->setEmailFactory($container['email/factory']);
    }

    /**
     * Hook called before saving the item.
     *
     * @return boolean
     * @see \Charcoal\Queue\QueueItemTrait::preSaveQueueItem()
     */
    protected function preSave():  bool
    {
        parent::preSave();

        $this->preSaveQueueItem();

        return true;
    }

    /**
     * @return FactoryInterface
     */
    protected function emailFactory(): FactoryInterface
    {
        return $this->emailFactory;
    }

    /**
     * @param FactoryInterface $factory The factory to create email objects.
     * @return void
     */
    private function setEmailFactory(FactoryInterface $factory): void
    {
        $this->emailFactory = $factory;
    }
}
