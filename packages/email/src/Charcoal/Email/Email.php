<?php

declare(strict_types=1);

namespace Charcoal\Email;

use Charcoal\Email\Exception\EmailNotSentException;
use Charcoal\Email\Services\Tracker;
use Exception;
use PDOException;
use InvalidArgumentException;
// From 'psr/log' (PSR-3)
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
// From 'phpmailer/phpmailer'
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
// From 'charcoal/config'
use Charcoal\Config\AbstractEntity;
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal/factory'
use Charcoal\Factory\FactoryInterface;
// From 'charcoal/view'
use Charcoal\View\ViewableInterface;
use Charcoal\View\ViewableTrait;
// From 'charcoal/queue'
use Charcoal\Queue\QueueableInterface;
use Charcoal\Queue\QueueableTrait;

/**
 * Default implementation of the `EmailInterface`.
 */
class Email extends AbstractEntity implements
    ConfigurableInterface,
    EmailInterface,
    LoggerAwareInterface,
    QueueableInterface,
    ViewableInterface
{
    use ConfigurableTrait;
    use LoggerAwareTrait;
    use QueueableTrait;
    use ViewableTrait;
    use EmailAwareTrait;

    /**
     * The campaign ID.
     */
    private ?string $campaign = null;

    /**
     * The recipient email address(es).
     */
    private array $to = [];

    /**
     * The CC recipient email address(es).
     */
    private array $cc = [];

    /**
     * The BCC recipient email address(es).
     */
    private array $bcc = [];

    /**
     * The sender's email address.
     */
    private ?string $from = null;

    /**
     * The email address to reply to the message.
     *
     * @var string
     */
    private $replyTo;

    /**
     * The email subject.
     */
    private ?string $subject = null;

    /**
     * The HTML message body.
     */
    private ?string $msgHtml = null;

    /**
     * The plain-text message body.
     */
    private ?string $msgTxt = null;

    /**
     * @var array
     */
    private $attachments = [];

    /**
     * Whether the email should be logged.
     *
     * @var boolean
     */
    private $logEnabled;

    /**
     * Whether the email should be tracked.
     *
     * @var boolean
     */
    private $trackOpenEnabled;

    /**
     * @var boolean
     */
    private $trackLinksEnabled;

    /**
     * The data to pass onto the view controller.
     */
    private array $templateData = [];

    private \PHPMailer\PHPMailer\PHPMailer $phpMailer;

    private \Charcoal\Factory\FactoryInterface $templateFactory;

    private \Charcoal\Factory\FactoryInterface $queueItemFactory;

    private \Charcoal\Factory\FactoryInterface $logFactory;

    private \Charcoal\Email\Services\Tracker $tracker;

    /**
     * Construct a new Email object with the given dependencies.
     *
     * - `logger` a PSR-3 logger.
     * - `view` a charcoal view for template rendering.
     * - `config` a charcoal config containing email settings.
     * - `template_factory` a charcoal model factory to create templates.
     * - `queue_item_factory` a charcoal model factory to create queue item.
     * - `log_factory` a charcoal model factory to create email logs.
     *
     * @param array $data Dependencies and settings.
     */
    public function __construct(array $data)
    {
        $this->phpMailer = new PHPMailer(true);
        $this->setLogger($data['logger']);
        $this->setView($data['view']);
        $this->setConfig($data['config']);
        $this->setTemplateFactory($data['template_factory']);
        $this->setQueueItemFactory($data['queue_item_factory']);
        $this->setLogFactory($data['log_factory']);
        $this->setTracker($data['tracker']);
    }

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     */
    public function setCampaign(string $campaign): static
    {
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * Get the campaign identifier.
     *
     * If it has not been explicitely set, it will be auto-generated (with uniqid).
     */
    public function campaign(): string
    {
        if ($this->campaign === null) {
            $this->campaign = $this->generateCampaign();
        }
        return $this->campaign;
    }

    /**
     * Set the recipient email address(es).
     *
     * @param string|array $email The recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function setTo($email): static
    {
        if (is_string($email)) {
            $email = [ $email ];
        }

        if (!is_array($email)) {
            throw new InvalidArgumentException(
                'Must be an array of recipients.'
            );
        }

        $this->to = [];

        // At this point, `$email` can be an _email array_ or an _array of emails_...
        if (isset($email['email'])) {
            // Means we're not dealing with multiple emails
            $this->addTo($email);
        } else {
            foreach ($email as $recipient) {
                $this->addTo($recipient);
            }
        }

        return $this;
    }

    /**
     * Add a recipient email address.
     *
     * @param  mixed $email The recipient email address to add.
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function addTo($email): static
    {
        $this->to[] = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the recipient's email addresses.
     *
     * @return string[]
     */
    public function to(): array
    {
        return $this->to;
    }

    /**
     * Set the carbon copy (CC) recipient email address(es).
     *
     * @param string|array $email The CC recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function setCc($email): static
    {
        if (is_string($email)) {
            $email = [ $email ];
        }

        if (!is_array($email)) {
            throw new InvalidArgumentException(
                'Must be an array of CC recipients.'
            );
        }

        $this->cc = [];

        // At this point, `$email` can be an _email array_ or an _array of emails_...
        if (isset($email['email'])) {
            // Means we're not dealing with multiple emails
            $this->addCc($email);
        } else {
            foreach ($email as $recipient) {
                $this->addCc($recipient);
            }
        }

        return $this;
    }

    /**
     * Add a CC recipient email address.
     *
     * @param mixed $email The CC recipient email address to add.
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function addCc($email): static
    {
        $this->cc[] = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the CC recipient's email address.
     *
     * @return string[]
     */
    public function cc(): array
    {
        return $this->cc;
    }

    /**
     * Set the blind carbon copy (BCC) recipient email address(es).
     *
     * @param string|array $email The BCC recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function setBcc($email): static
    {
        if (is_string($email)) {
            // Means we have a straight email
            $email = [ $email ];
        }

        if (!is_array($email)) {
            throw new InvalidArgumentException(
                'Must be an array of BCC recipients.'
            );
        }

        $this->bcc = [];

        // At this point, `$email` can be an _email array_ or an _array of emails_...
        if (isset($email['email'])) {
            // Means we're not dealing with multiple emails
            $this->addBcc($email);
        } else {
            foreach ($email as $recipient) {
                $this->addBcc($recipient);
            }
        }

        return $this;
    }

    /**
     * Add a BCC recipient email address.
     *
     * @param mixed $email The BCC recipient email address to add.
     * @throws InvalidArgumentException If the email address is invalid.
     */
    public function addBcc($email): static
    {
        $this->bcc[] = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the BCC recipient's email address.
     *
     * @return string[]
     */
    public function bcc(): array
    {
        return $this->bcc;
    }

    /**
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     * @throws InvalidArgumentException If the email is not a string or an array.
     * @todo   Implement optional "Sender" field.
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
        if ($this->from === null) {
            $this->setFrom($this->config()->defaultFrom());
        }
        return $this->from;
    }

    /**
     * Set email address to reply to the message.
     *
     * @param  mixed $email The sender's "Reply-To" email address.
     * @throws InvalidArgumentException If the email is not a string or an array.
     */
    public function setReplyTo($email): static
    {
        $this->replyTo = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get email address to reply to the message.
     *
     * @return string
     */
    public function replyTo()
    {
        if ($this->replyTo === null) {
            $this->replyTo = $this->config()->defaultReplyTo();
        }
        return $this->replyTo;
    }

    /**
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get the email subject.
     *
     * @return string The emails' subject.
     */
    public function subject(): string
    {
        return $this->subject;
    }

    /**
     * Set the email's HTML message body.
     *
     * @param  string $body The HTML message body.
     */
    public function setMsgHtml(string $body): static
    {
        $this->msgHtml = $body;
        return $this;
    }

    /**
     * Get the email's HTML message body.
     *
     * If the message is not explitely set, it will be
     * auto-generated from a template view.
     */
    public function msgHtml(): string
    {
        if ($this->msgHtml === null) {
            $this->msgHtml = $this->generateMsgHtml();
        }
        return $this->msgHtml;
    }

    /**
     * Set the email's plain-text message body.
     *
     * @param string $body The message's text body.
     */
    public function setMsgTxt(string $body): static
    {
        $this->msgTxt = $body;
        return $this;
    }

    /**
     * Get the email's plain-text message body.
     *
     * If the plain-text message is not explitely set,
     * it will be auto-generated from the HTML message.
     */
    public function msgTxt(): string
    {
        if ($this->msgTxt === null) {
            $this->msgTxt = $this->stripHtml($this->msgHtml());
        }
        return $this->msgTxt;
    }

    /**
     * Set the email's attachments.
     *
     * @param  array $attachments The file attachments.
     */
    public function setAttachments(array $attachments): static
    {
        foreach ($attachments as $att) {
            $this->addAttachment($att);
        }
        return $this;
    }

    /**
     * Add an attachment to the email.
     *
     * @param  mixed $attachment A single file attachment.
     */
    public function addAttachment($attachment): static
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * Get the email's attachments.
     *
     * @return array
     */
    public function attachments()
    {
        return $this->attachments;
    }

    /**
     * Enable or disable logging for this particular email.
     *
     * @param  boolean $log The log-enabled flag.
     */
    public function setLogEnabled($log): static
    {
        $this->logEnabled = (bool)$log;
        return $this;
    }

    /**
     * Determine if logging is enabled for this particular email.
     */
    public function logEnabled(): bool
    {
        if ($this->logEnabled === null) {
            $this->logEnabled = $this->config()->defaultLogEnabled();
        }
        return $this->logEnabled;
    }

    /**
     * Enable or disable email open tracking for this particular email.
     *
     * @param boolean $track The track flag.
     */
    public function setTrackOpenEnabled($track): static
    {
        $this->trackOpenEnabled = (bool)$track;
        return $this;
    }

    /**
     * Determine if email open tracking is enabled for this particular email.
     */
    public function trackOpenEnabled(): bool
    {
        if ($this->trackOpenEnabled === null) {
            $this->trackOpenEnabled = $this->config()->defaultTrackOpenEnabled();
        }
        return $this->trackOpenEnabled;
    }

    /**
     * Enable or disable email links tracking for this particular email.
     *
     * @param boolean $track The track flag.
     */
    public function setTrackLinksEnabled($track): static
    {
        $this->trackLinksEnabled = (bool)$track;
        return $this;
    }

    /**
     * Determine if email links tracking is enabled for this particular email.
     */
    public function trackLinksEnabled(): bool
    {
        if ($this->trackLinksEnabled === null) {
            $this->trackLinksEnabled = $this->config()->defaultTrackLinksEnabled();
        }
        return $this->trackLinksEnabled;
    }


    /**
     * Send the email to all recipients
     *
     * @return boolean Success / Failure.
     * @throws EmailNotSentException On email sending failure.
     * @todo Implement methods and property for toggling rich-text vs. plain-text
     *       emails (`$mail->isHTML(true)`).
     */
    public function send(): bool
    {
        $this->logger->debug(
            'Attempting to send an email',
            $this->to()
        );

        $mail = $this->phpMailer;

        try {
            $this->setSmtpOptions($mail);

            $mail->CharSet = 'UTF-8';

            // Setting reply-to field, if required.
            $replyTo = $this->replyTo();
            if ($replyTo) {
                $replyArr = $this->emailToArray($replyTo);
                $mail->addReplyTo($replyArr['email'], $replyArr['name']);
            }

            // Setting from (sender) field.
            $from = $this->from();
            $fromArr = $this->emailToArray($from);
            $mail->setFrom($fromArr['email'], $fromArr['name']);

            // Setting to (recipients) field(s).
            $to = $this->to();
            foreach ($to as $recipient) {
                $toArr = $this->emailToArray($recipient);
                $mail->addAddress($toArr['email'], $toArr['name']);
            }

            // Setting cc (carbon-copy) field(s).
            $cc = $this->cc();
            foreach ($cc as $ccRecipient) {
                $ccArr = $this->emailToArray($ccRecipient);
                $mail->addCC($ccArr['email'], $ccArr['name']);
            }

            // Setting bcc (black-carbon-copy) field(s).
            $bcc = $this->bcc();
            foreach ($bcc as $bccRecipient) {
                $bccArr = $this->emailToArray($bccRecipient);
                $mail->addBCC($bccArr['email'], $bccArr['name']);
            }

            // Setting attachment(s), if required.
            $attachments = $this->attachments();
            foreach ($attachments as $att) {
                $mail->addAttachment($att);
            }

            $mail->isHTML(true);

            $logId = uniqid();

            if ($this->trackOpenEnabled()) {
                $this->tracker->addOpenTrackingImage($this, $logId);
            }
            if ($this->trackLinksEnabled()) {
                $this->tracker->replaceLinksWithTracker($this, $logId);
            }

            $mail->Subject = $this->subject();
            $mail->Body    = $this->msgHtml();
            $mail->AltBody = $this->msgTxt();
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Error sending email: %s', $e->getMessage())
            );
            return false;
        }

        try {
            $ret = $mail->send();
        } catch (PHPMailerException $e) {
            throw new EmailNotSentException($e->getMessage(), $e->getCode(), $e);
        }

        if ($this->logEnabled()) {
            try {
                $this->logSend($ret, $logId, $mail);
            } catch (PDOException $e) {
                $this->logger->error(
                    sprintf('Error logging email: %s', $e->getMessage())
                );
            }
        }

        return $ret;
    }

    /**
     * Set the SMTP's options for PHPMailer.
     *
     * @param PHPMailer $mail The PHPMailer to setup.
     * @return void
     */
    protected function setSmtpOptions(PHPMailer $mail)
    {
        $config = $this->config();
        if (!$config['smtp']) {
            return;
        }

        $this->logger->debug(
            sprintf('Using SMTP "%s" server to send email', $config['smtp_hostname'])
        );

        $mail->isSMTP();
        $mail->Host       = $config['smtp_hostname'];
        $mail->Port       = $config['smtp_port'];
        $mail->SMTPAuth   = $config['smtp_auth'];
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_security'];
    }

    /**
     * Enqueue the email for each recipient.
     *
     * @param mixed $ts A date/time to initiate the queue processing.
     */
    public function queue($ts = null): static
    {
        $recipients = $this->to();
        $author     = $this->from();
        $subject    = $this->subject();
        $msgHtml    = $this->msgHtml();
        $msgTxt     = $this->msgTxt();
        $campaign   = $this->campaign();
        $queueId    = $this->queueId();

        foreach ($recipients as $to) {
            if (is_string($to) && ($to !== '' && $to !== '0')) {
                $queueItem = $this->queueItemFactory()->create(EmailQueueItem::class);

                $queueItem->setTo($to);
                $queueItem->setFrom($author);
                $queueItem->setSubject($subject);
                $queueItem->setMsgHtml($msgHtml);
                $queueItem->setMsgTxt($msgTxt);
                $queueItem->setCampaign($campaign);
                $queueItem->setProcessingDate($ts);
                $queueItem->setQueueId($queueId);

                $queueItem->save();
            } else {
                $this->logger->warning('Could not queue email, null or empty value');
            }
        }

        return $this;
    }

    /**
     * Set the template data for the view.
     *
     * @param array $data The template data.
     * @return Email Chainable
     */
    public function setTemplateData(array $data): static
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * Get the template data for the view.
     */
    public function templateData(): array
    {
        return $this->templateData;
    }

    /**
     * Get the custom view controller for rendering
     * the email's HTML message.
     *
     * Unlike typical `ViewableInterface` objects, the view controller is not
     * the email itself but an external "email" template.
     *
     * @see    ViewableInterface::viewController()
     * @return \Charcoal\App\Template\TemplateInterface|array
     */
    public function viewController()
    {
        $templateIdent = $this->templateIdent();

        if (!$templateIdent) {
            return [];
        }

        $templateFactory = clone($this->templateFactory());
        $templateFactory->setDefaultClass(GenericEmailTemplate::class);
        $template = $templateFactory->create($templateIdent);

        $template->setData($this->templateData());

        return $template;
    }

    /**
     * @param FactoryInterface $factory The factory to use to create email template objects.
     * @return Email Chainable
     */
    protected function setTemplateFactory(FactoryInterface $factory): static
    {
        $this->templateFactory = $factory;
        return $this;
    }

    protected function templateFactory(): FactoryInterface
    {
        return $this->templateFactory;
    }

    /**
     * @param FactoryInterface $factory The factory to use to create email queue item objects.
     * @return Email Chainable
     */
    protected function setQueueItemFactory(FactoryInterface $factory): static
    {
        $this->queueItemFactory = $factory;
        return $this;
    }

    protected function queueItemFactory(): FactoryInterface
    {
        return $this->queueItemFactory;
    }

    /**
     * @param FactoryInterface $factory The factory to use to create log objects.
     * @return Email Chainable
     */
    protected function setLogFactory(FactoryInterface $factory): static
    {
        $this->logFactory = $factory;
        return $this;
    }

    protected function logFactory(): FactoryInterface
    {
        return $this->logFactory;
    }

    /**
     * @param Tracker $tracker Tracker service.
     */
    public function setTracker(Tracker $tracker): void
    {
        $this->tracker = $tracker;
    }

    /**
     * Get the email's HTML message from the template, if applicable.
     *
     * @see    ViewableInterface::render()
     */
    protected function generateMsgHtml(): string
    {
        $templateIdent = $this->templateIdent();

        return $templateIdent ? $this->render($templateIdent) : '';
    }

    /**
     * Generates a unique identifier ideal for a campaign ID.
     */
    protected function generateCampaign(): string
    {
        return uniqid();
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter(string $key): string
    {
        $getter = $key;
        return $this->camelize($getter);
    }

    /**
     * Allow an object to define how the key setter are called.
     *
     * @param string $key The key to get the setter from.
     * @return string The setter method name, for a given key.
     */
    protected function setter(string $key): string
    {
        $setter = 'set_' . $key;
        return $this->camelize($setter);
    }

    /**
     * Convert an HTML string to plain-text.
     *
     * @param string $html The HTML string to convert.
     * @return string The resulting plain-text string.
     */
    protected function stripHtml(string $html): string
    {
        $str = html_entity_decode($html);

        // Strip HTML (Replace br with newline, remove "invisible" tags and strip other tags)
        $str = preg_replace('#<br[^>]*?>#siu', "\n", $str);
        $str = preg_replace(
            [
                '#<applet[^>]*?.*?</applet>#siu',
                '#<embed[^>]*?.*?</embed>#siu',
                '#<head[^>]*?>.*?</head>#siu',
                '#<noframes[^>]*?.*?</noframes>#siu',
                '#<noscript[^>]*?.*?</noscript>#siu',
                '#<noembed[^>]*?.*?</noembed>#siu',
                '#<object[^>]*?.*?</object>#siu',
                '#<script[^>]*?.*?</script>#siu',
                '#<style[^>]*?>.*?</style>#siu'
            ],
            '',
            (string)$str
        );
        $str = strip_tags((string)$str);

        // Trim whitespace
        $str = str_replace("\t", '', $str);
        $str = preg_replace('#\n\r|\r\n#', "\n", $str);
        $str = preg_replace('#\n{3,}#', "\n\n", (string)$str);
        $str = preg_replace('/ {2,}/', ' ', (string)$str);
        $str = implode("\n", array_map(trim(...), explode("\n", (string)$str)));
        return trim($str) . "\n";
    }

    /**
     * Log the send event for each recipient.
     *
     * @param  boolean   $result Success or failure.
     * @param  string    $logId  Email log id.
     * @param  PHPMailer $mailer The raw mailer.
     */
    protected function logSend(bool $result, string $logId, PHPMailer $mailer): void
    {
        if (!$result) {
            $this->logger->error('Email could not be sent.');
        } else {
            $this->logger->debug(
                sprintf('Email "%s" sent successfully.', $this->subject()),
                $this->to()
            );
        }

        $recipients = array_merge(
            $this->to(),
            $this->cc(),
            $this->bcc()
        );

        foreach ($recipients as $to) {
            $log = $this->logFactory()->create(EmailLog::class);

            $log->setId($logId);
            $log->setQueueId($this->queueId());
            $log->setMessageId($mailer->getLastMessageId());
            $log->setCampaign($this->campaign());
            $log->setSendTs('now');
            $log->setFrom($mailer->From);
            $log->setTo($to);
            $log->setSubject($this->subject());

            if (!empty($mailer->getSMTPInstance()->getError()['smtp_code'])) {
                $log->setErrorCode($mailer->getSMTPInstance()->getError()['smtp_code']);
            }

            $log->save();
        }
    }

    /**
     * Temporary hack to fulfills the Configurable Interface.
     */
    public function createConfig(): \Charcoal\Email\EmailConfig
    {
        // This should really be avoided.
        $this->logger->warning('AbstractEmail::createConfig() was called, but should not.');
        return new \Charcoal\Email\EmailConfig();
    }
}
