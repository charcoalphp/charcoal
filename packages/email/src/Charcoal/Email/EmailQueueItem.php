<?php

declare(strict_types=1);

namespace Charcoal\Email;

use Charcoal\Email\Exception\EmailNotSentException;
use Exception;
// From 'pimple/pimple'
use Pimple\Container;
// From 'charcoal/factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal/core'
use Charcoal\Model\AbstractModel;
// From 'charcoal/queue'
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
     */
    private ?string $to = null;

    /**
     * The sender's email address.
     */
    private ?string $from = null;

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

    private \Charcoal\Factory\FactoryInterface $emailFactory;

    /**
     * Get the primary key that uniquely identifies each queue item.
     */
    #[\Override]
    public function key(): string
    {
        return 'id';
    }

    /**
     * Set the recipient's email address.
     *
     * @param  string|array $email An email address.
     */
    public function setTo($email): static
    {
        try {
            $this->to = $this->parseEmail($email);
        } catch (Exception) {
            $this->logger->warning(sprintf('Invalid "to" email: "%s"', strval($email)));
        }

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
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     */
    public function setFrom($email): static
    {
        try {
            $this->from = $this->parseEmail($email);
        } catch (Exception) {
            $this->logger->warning(sprintf('Invalid "from" email: "%s"', strval($email)));
        }

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
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     */
    public function setSubject($subject): static
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
     */
    public function setMsgHtml($body): static
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
     */
    public function setMsgTxt($body): static
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
     */
    public function setCampaign($campaign): static
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
     * @param callable|null $alwaysCallback  An optional callback routine executed after the item is processed.
     * @param callable|null $successCallback An optional callback routine executed when the item is resolved.
     * @param callable|null $failureCallback An optional callback routine executed when the item is rejected.
     * @return boolean|null Returns TRUE i this item was successfully processed,
     *                                       FALSE on failure or if an error occurs, NULL if this item is already
     *                                       processed.
     */
    public function process(
        ?callable $alwaysCallback = null,
        ?callable $successCallback = null,
        ?callable $failureCallback = null
    ): ?bool {
        $email = $this->emailFactory()->create('email');
        $email->setData($this->data());

        try {
            $result = $email->send();

            $this->setStatus(($result) ? self::STATUS_SUCCESS : self::STATUS_FAILED);
        } catch (EmailNotSentException $e) {
            $this->logProcessingException($e);
            $this->setStatus(self::STATUS_RETRY);

            $result = false;
        } catch (Exception $e) {
            $this->logProcessingException($e);
            $this->setStatus(self::STATUS_FAILED);

            $result = false;
        }

        $propsToUpdate = [];

        if ($result) {
            // Clear cumbersome DB data
            $this->setMsgHtml(null)
                 ->setMsgTxt(null);
            $propsToUpdate[] = 'msg_html';
            $propsToUpdate[] = 'msg_txt';
            if ($successCallback !== null) {
                $successCallback($this);
            }
        } elseif ($failureCallback !== null) {
            $failureCallback($this);
        }

        $this->update(array_merge([
            'status',
        ], $propsToUpdate));

        if ($alwaysCallback !== null) {
            $alwaysCallback($this);
        }

        return $result;
    }

    /**
     * @param Container $container Pimple DI container.
     */
    #[\Override]
    protected function setDependencies(Container $container): void
    {
        parent::setDependencies($container);
        $this->setEmailFactory($container['email/factory']);
    }

    /**
     * Hook called before saving the item.
     *
     * @see \Charcoal\Queue\QueueItemTrait::preSaveQueueItem()
     */
    #[\Override]
    protected function preSave(): bool
    {
        parent::preSave();

        $this->preSaveQueueItem();

        return true;
    }

    protected function emailFactory(): FactoryInterface
    {
        return $this->emailFactory;
    }

    /**
     * @param FactoryInterface $factory The factory to create email objects.
     */
    private function setEmailFactory(FactoryInterface $factory): void
    {
        $this->emailFactory = $factory;
    }
}
