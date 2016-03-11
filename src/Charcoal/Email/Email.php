<?php

namespace Charcoal\Email;

// Dependencies from `PHP`
use \Exception;
use \InvalidArgumentException;

// PSR-3 (logger) dependencies
use \Psr\Log\LoggerAwareInterface;
use \Psr\Log\LoggerAwareTrait;

// From `phpmailer/phpmailer`
use \PHPMailer\PHPMailer\PHPMailer;

// Module `charcoal-config` dependencies
use \Charcoal\Config\ConfigurableInterface;
use \Charcoal\Config\ConfigurableTrait;

// Module `charcoal-view` dependencies
use \Charcoal\View\GenericView;
use \Charcoal\View\ViewableInterface;
use \Charcoal\View\ViewableTrait;

// Module `charcoal-app` dependencies
use \Charcoal\App\Template\TemplateFactory;

// Intra module (`charcoal-email`) dependencies
use \Charcoal\Email\Queue\QueueableInterface;
use \Charcoal\Email\Queue\QueueableTrait;
use \Charcoal\Email\EmailInterface;
use \Charcoal\Email\EmailConfig;
use \Charcoal\Email\EmailLog;

/**
 * Default implementation of the `EmailInterface`.
 */
class Email implements
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
     *
     * @var string $campaign
     */
    private $campaign;

    /**
     * The recipient email address(es).
     *
     * @var array $to
     */
    private $to = [];

    /**
     * The CC recipient email address(es).
     *
     * @var array $cc
     */
    private $cc = [];

    /**
     * The BCC recipient email address(es).
     *
     * @var array $bcc
     */
    private $bcc = [];

    /**
     * The sender's email address.
     *
     * @var string $from
     */
    private $from;

    /**
     * The email address to reply to the message.
     *
     * @var string $replyTo
     */
    private $replyTo;

    /**
     * The email subject.
     *
     * @var string $subject
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
     * @var array $attachments
     */
    private $attachments = [];

    /**
     * Whether the email should be logged.
     *
     * @var boolean $log
     */
    private $log;

    /**
     * Whether the email should be tracked.
     *
     * @var boolean $track
     */
    private $track;

    /**
     * The data to pass onto the view controller.
     *
     * @var array $templateData
     */
    private $templateData = [];

    /**
     * @var PHPMailer PHP Mailer instance.
     */
    private $phpMailer;

    /**
     * Construct a new Email object.
     *
     * @param array $data Dependencies and settings.
     */
    public function __construct(array $data)
    {
        $this->phpMailer = $data['phpmailer'];
        $this->setLogger($data['logger']);

        if (isset($data['view'])) {
            $this->setView($data['view']);
        }
        if (isset($data['config'])) {
            $this->setConfig($data['config']);
        }
    }

    /**
     * Set the email's data.
     *
     * @param array $data The data to set.
     * @return Email Chainable
     */
    public function setData(array $data)
    {
        foreach ($data as $prop => $val) {
            $func = [$this, $this->setter($prop)];
            if (is_callable($func)) {
                call_user_func($func, $val);
                unset($data[$prop]);
            } else {
                $this->{$prop} = $val;
            }
        }

        return $this;
    }

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     * @throws InvalidArgumentException If the campaign is invalid.
     * @return EmailInterface Chainable
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
     * If it has not been explicitely set, it will be auto-generated (with uniqid).
     *
     * @return string
     */
    public function campaign()
    {
        if ($this->campaign === null) {
            $this->campaign = $this->generateCampaign();
        }

        return $this->campaign;
    }

    /**
     * Generates a unique identifier ideal for a campaign ID.
     *
     * @return string
     */
    protected function generateCampaign()
    {
        return uniqid();
    }

    /**
     * Set the recipient email address(es).
     *
     * @param string|array $email The recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailInterface Chainable
     */
    public function setTo($email)
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
     * @return EmailInterface Chainable
     */
    public function addTo($email)
    {
        if (is_string($email)) {
            $this->to[] = $email;
        } elseif (is_array($email)) {
            $this->to[] = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Can not set to: email must be an array or a string'
            );
        }

        return $this;
    }

    /**
     * Get the recipient's email address.
     *
     * @return string[]
     */
    public function to()
    {
        return $this->to;
    }

    /**
     * Set the carbon copy (CC) recipient email address(es).
     *
     * @param string|array $email The CC recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailInterface Chainable
     */
    public function setCc($email)
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
     * @return EmailInterface Chainable
     */
    public function addCc($email)
    {
        if (is_string($email)) {
            $this->cc[] = $email;
        } elseif (is_array($email)) {
            $this->cc[] = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Can not set to: email must be an array or a string'
            );
        }

        return $this;
    }

    /**
     * Get the CC recipient's email address.
     *
     * @return string[]
     */
    public function cc()
    {
        return $this->cc;
    }

    /**
     * Set the blind carbon copy (BCC) recipient email address(es).
     *
     * @param string|array $email The BCC recipient email address(es).
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailInterface Chainable
     */
    public function setBcc($email)
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
     * @return EmailInterface Chainable
     */
    public function addBcc($email)
    {
        if (is_string($email)) {
            $this->bcc[] = $email;
        } elseif (is_array($email)) {
            $this->bcc[] = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Can not set to: email must be an array or a string'
            );
        }

        return $this;
    }

    /**
     * Get the BCC recipient's email address.
     *
     * @return string[]
     */
    public function bcc()
    {
        return $this->bcc;
    }

    /**
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     * @throws InvalidArgumentException If the email is not a string or an array.
     * @return EmailInterface Chainable
     * @todo   Implement optional "Sender" field.
     */
    public function setFrom($email)
    {
        if (is_array($email)) {
            $this->from = $this->emailFromArray($email);
        } elseif (is_string($email)) {
            $this->from = $email;
        } else {
            throw new InvalidArgumentException(
                'Can not set from: email must be an array or a string'
            );
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
     * @return EmailInterface Chainable
     */
    public function setReplyTo($email)
    {
        if (is_array($email)) {
            $this->replyTo = $this->emailFromArray($email);
        } elseif (is_string($email)) {
            $this->replyTo = $email;
        } else {
            throw new InvalidArgumentException(
                'Can not set reply-to: email must be an array or a string'
            );
        }

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
     * @throws InvalidArgumentException If the subject is not a string.
     * @return EmailInterface Chainable
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
     * @return string The emails' subject.
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
     * @return EmailInterface Chainable
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
     * If the message is not explitely set, it will be
     * auto-generated from a template view.
     *
     * @return string
     */
    public function msgHtml()
    {
        if ($this->msgHtml === null) {
            $this->msgHtml = $this->generateMsgHtml();
        }
        return $this->msgHtml;
    }

    /**
     * Get the email's HTML message from the template, if applicable.
     *
     * @see    ViewableInterface::renderTemplate()
     * @return string
     */
    protected function generateMsgHtml()
    {
        $templateIdent = $this->templateIdent();

        if (!$templateIdent) {
            $message = '';
        } else {
            $message = $this->renderTemplate($templateIdent);
        }

        return $message;
    }

    /**
     * Set the email's plain-text message body.
     *
     * @param string $body The message's text body.
     * @throws InvalidArgumentException If the parameter is invalid.
     * @return EmailInterface Chainable
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
     * If the plain-text message is not explitely set,
     * it will be auto-generated from the HTML message.
     *
     * @return string
     */
    public function msgTxt()
    {
        if ($this->msgTxt === null) {
            $this->msgTxt = $this->stripHtml($this->msgHtml());
        }

        return $this->msgTxt;
    }

    /**
     * Convert an HTML string to plain-text.
     *
     * @param string $html The HTML string to convert.
     * @return string The resulting plain-text string.
     */
    protected function stripHtml($html)
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
            $str
        );
        $str = strip_tags($str);

        // Trim whitespace
        $str = str_replace("\t", '', $str);
        $str = preg_replace('#\n\r|\r\n#', "\n", $str);
        $str = preg_replace('#\n{3,}#', "\n\n", $str);
        $str = preg_replace('/ {2,}/', ' ', $str);
        $str = implode("\n", array_map('trim', explode("\n", $str)));
        $str = trim($str)."\n";
        return $str;
    }

    /**
     * Set the email's attachments.
     *
     * @param  array $attachments The file attachments.
     * @return EmailInterface Chainable
     */
    public function setAttachments(array $attachments)
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
     * @return EmailInterface Chainable
     */
    public function addAttachment($attachment)
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
     * @param  boolean $log The log flag.
     * @return EmailInterface Chainable
     */
    public function setLog($log)
    {
        $this->log = !!$log;
        return $this;
    }

    /**
     * Determine if logging is enabled for this particular email.
     *
     * @return boolean
     */
    public function log()
    {
        if ($this->log === null) {
            $this->log = $this->config()->defaultLog();
        }
        return $this->log;
    }

    /**
     * Enable or disable tracking for this particular email.
     *
     * @param boolean $track The track flag.
     * @return EmailInterface Chainable
     */
    public function setTrack($track)
    {
        $this->track = !!$track;
        return $this;
    }

    /**
     * Determine if tracking is enabled for this particular email.
     *
     * @return boolean
     */
    public function track()
    {
        if ($this->track === null) {
            $this->track = $this->config()->defaultTrack();
        }
        return $this->track;
    }

    /**
     * Send the email to all recipients
     *
     * @return boolean Success / Failure.
     * @todo Implement methods and property for toggling rich-text vs. plain-text
     *       emails (`$mail->isHTML(true)`).
     */
    public function send()
    {
        $this->logger->debug(
            'Attempting to send an email',
            $this->to()
        );

        $mail = $this->phpMailer;

        try {
            $this->setSmtpOptions($mail);

            $mail->CharSet = 'UTF-8';

            // Setting FROM
            $from = $this->from();

            // From DOC, $name = ''
            // Set from defines the default vars
            $mail->setFrom($from['email'], $from['name']);

            $to = $this->to();

            foreach ($to as $recipient) {
                // Default name set in setTo()
                $mail->addAddress($recipient['email'], $recipient['name']);
            }

            $replyTo = $this->replyTo();
            if ($replyTo) {
                // Default name set in setReplyTo()
                $mail->addReplyTo($replyTo['email'], $replyTo['name']);
            }

            $cc = $this->bcc();
            foreach ($cc as $ccRecipient) {
                // Default name set in addCc()
                $mail->addCC($ccRecipient['email'], $ccRecipient['name']);
            }

            $bcc = $this->bcc();
            foreach ($bcc as $bccRecipient) {
                // Default name set in addBcc()
                $mail->addBCC($bccRecipient['email'], $bccRecipient['name']);
            }

            $attachments = $this->attachments();
            foreach ($attachments as $att) {
                $mail->addAttachment($att);
            }

            $mail->isHTML(true);

            $mail->Subject = $this->subject();
            $mail->Body    = $this->msgHtml();
            $mail->AltBody = $this->msgTxt();

            $ret = $mail->send();

            $this->logSend($ret, $mail);

            return $ret;
        } catch (Exception $e) {
            $this->logger->error(
                sprintf('Error sending email: %s', $e->getMessage())
            );
        }
    }

    /**
     * Set the SMTP's options for PHPMailer.
     *
     * @param PHPMailer $mail The PHPMailer to setup.
     * @return void
     */
    public function setSmtpOptions(PHPMailer $mail)
    {
        $config = $this->config();
        if (!$config['smtp']) {
            return;
        }

        $this->logger->debug(
            sprintf('Using SMTP "%s" server to send email', $config['smtp_hostname'])
        );

        $mail->IsSMTP();
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
     * @return boolean Success / Failure.
     */
    public function queue($ts = null)
    {
        $recipients = $this->to();
        $author     = $this->from();
        $subject    = $this->subject();
        $msgHtml    = $this->msgHtml();
        $msgTxt     = $this->msgTxt();
        $campaign   = $this->campaign();
        $queueId    = $this->queueId();

        foreach ($recipients as $to) {
            $queueItem = new EmailQueueItem();

            $queueItem->setTo($to['email']);
            $queueItem->setFrom($author['email']);
            $queueItem->setSubject($subject);
            $queueItem->setMsgHtml($msgHtml);
            $queueItem->setMsgTxt($msgTxt);
            $queueItem->setCampaign($campaign);
            $queueItem->setProcessingDate($ts);
            $queueItem->setQueueId($queueId);

            $res = $queueItem->save();
        }

        return true;
    }

    /**
     * Log the send event for each recipient.
     *
     * @param  boolean $result Success or failure.
     * @param  mixed   $mailer The raw mailer.
     * @return void
     */
    protected function logSend($result, $mailer)
    {
        if ($this->log() === false) {
            return;
        }

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
            $log = new EmailLog([
                'logger' => $this->logger
            ]);

            $log->setType('email');
            $log->setAction('send');

            $log->setRawResponse($mailer);

            $log->setMessageId($mailer->getLastMessageId());
            $log->setCampaign($this->campaign());

            $log->setSendDate('now');

            $log->setFrom($mailer->From);
            $log->setTo($to['email']);
            $log->setSubject($this->subject());

            $log->save();
        }

    }

    /**
     * Log the queue event.
     *
     * @return void
     * @todo Implement log qeueing.
     */
    protected function logQueue()
    {

    }

    /**
     * Set the template data for the view.
     *
     * @param array $data The template data.
     * @return Email Chainable
     */
    public function setTemplateData(array $data)
    {
        $this->templateData = $data;
        return $this;
    }

    /**
     * Get the template data for the view.
     *
     * @return array
     */
    public function templateData()
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
     * @return TemplateInterface|array
     */
    public function viewController()
    {
        $templateIdent = $this->templateIdent();

        if (!$templateIdent) {
            return [];
        }

        $templateFactory = new TemplateFactory();
        $templateFactory->setDefaultClass('charcoal/email/generic-email');
        $template = $templateFactory->create($templateIdent, [
            'logger' => $this->logger
        ]);

        $template->setData($this->templateData());

        return $template;
    }

    /**
     * Allow an object to define how the key getter are called.
     *
     * @param string $key The key to get the getter from.
     * @return string The getter method name, for a given key.
     */
    protected function getter($key)
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
    protected function setter($key)
    {
        $setter = 'set_'.$key;
        return $this->camelize($setter);

    }

    /**
     * Transform a snake_case string to camelCase.
     *
     * @param string $str The snake_case string to camelize.
     * @return string The camelCase string.
     */
    private function camelize($str)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $str))));
    }

    /**
     * Temporary hack to fulfills the Configurable Interface.
     *
     * @throws Exception If this function is ever called (obsolete).
     * @return void
     */
    public function createConfig()
    {
        throw new Exception(
            'Deprecated method'
        );
    }
}
