<?php

declare(strict_types=1);

namespace Charcoal\Email;

/**
 *
 */
interface EmailInterface
{
    /**
     * Set the email's data.
     *
     * @param array $data The data to set.
     * @return Email Chainable
     */
    public function setData(array $data);

    /**
     * Set the campaign ID.
     *
     * @param  string $campaign The campaign identifier.
     * @return EmailInterface Chainable
     */
    public function setCampaign(string $campaign);

    /**
     * Get the campaign identifier.
     *
     * @return string
     */
    public function campaign();

    /**
     * Set the recipient email address(es).
     *
     * @param string|array $email The recipient email address(es).
     * @return EmailInterface Chainable
     */
    public function setTo($email);

    /**
     * Add a recipient email address.
     *
     * @param  mixed $email The recipient email address to add.
     * @return EmailInterface Chainable
     */
    public function addTo($email);

    /**
     * Get the recipient's email address.
     *
     * @return string[]
     */
    public function to();

    /**
     * Set the carbon copy (CC) recipient email address(es).
     *
     * @param string|array $email The CC recipient email address(es).
     * @return EmailInterface Chainable
     */
    public function setCc($email);

    /**
     * Add a CC recipient email address.
     *
     * @param mixed $email The CC recipient email address to add.
     * @return EmailInterface Chainable
     */
    public function addCc($email);

    /**
     * Get the CC recipient's email address.
     *
     * @return string[]
     */
    public function cc();

    /**
     * Set the blind carbon copy (BCC) recipient email address(es).
     *
     * @param string|array $email The BCC recipient email address(es).
     * @return EmailInterface Chainable
     */
    public function setBcc($email);

    /**
     * Add a BCC recipient email address.
     *
     * @param mixed $email The BCC recipient email address to add.
     * @return EmailInterface Chainable
     */
    public function addBcc($email);

    /**
     * Get the BCC recipient's email address.
     *
     * @return string[]
     */
    public function bcc();

    /**
     * Set the sender's email address.
     *
     * @param  string|array $email An email address.
     * @return EmailInterface Chainable
     */
    public function setFrom($email);

    /**
     * Get the sender's email address.
     *
     * @return string
     */
    public function from();

    /**
     * Set email address to reply to the message.
     *
     * @param  mixed $email The sender's "Reply-To" email address.
     * @return EmailInterface Chainable
     */
    public function setReplyTo($email);

    /**
     * Get email address to reply to the message.
     *
     * @return string
     */
    public function replyTo();

    /**
     * Set the email subject.
     *
     * @param  string $subject The email subject.
     * @return EmailInterface Chainable
     */
    public function setSubject(string $subject);

    /**
     * Get the email subject.
     *
     * @return string The emails' subject.
     */
    public function subject(): string;

    /**
     * Set the email's HTML message body.
     *
     * @param  string $body The HTML message body.
     * @return EmailInterface Chainable
     */
    public function setMsgHtml(string $body);

    /**
     * Get the email's HTML message body.
     *
     * @return string
     */
    public function msgHtml(): string;

    /**
     * Set the email's plain-text message body.
     *
     * @param string $body The message's text body.
     * @return EmailInterface Chainable
     */
    public function setMsgTxt(string $body);

    /**
     * Get the email's plain-text message body.
     *
     * @return string
     */
    public function msgTxt(): string;

    /**
     * Set the email's attachments.
     *
     * @param  array $attachments The file attachments.
     * @return EmailInterface Chainable
     */
    public function setAttachments(array $attachments);

    /**
     * Add an attachment to the email.
     *
     * @param  mixed $attachment A single file attachment.
     * @return EmailInterface Chainable
     */
    public function addAttachment($attachment);

    /**
     * Get the email's attachments.
     *
     * @return array
     */
    public function attachments();

    /**
     * Enable or disable logging for this particular email.
     *
     * @param  boolean $log The log flag.
     * @return EmailInterface Chainable
     */
    public function setLogEnabled($log);

    /**
     * Determine if logging is enabled for this particular email.
     *
     * @return boolean
     */
    public function logEnabled(): bool;

    /**
     * Enable or disable tracking for this particular email.
     *
     * @param boolean $track The track flag.
     * @return EmailInterface Chainable
     */
    public function setTrackOpenEnabled($track);

    /**
     * Determine if tracking is enabled for this particular email.
     *
     * @return boolean
     */
    public function trackLinksEnabled(): bool;

    /**
     * Enable or disable tracking for this particular email.
     *
     * @param boolean $track The track flag.
     * @return EmailInterface Chainable
     */
    public function setTrackLinksEnabled($track);

    /**
     * Determine if tracking is enabled for this particular email.
     *
     * @return boolean
     */
    public function trackOpenEnabled(): bool;

    /**
     * Send the email to all recipients.
     *
     * @return boolean Success / Failure.
     */
    public function send(): bool;

    /**
     * Enqueue the email for each recipient.
     *
     * @param mixed $ts A date/time to initiate the queue processing.
     * @return self
     */
    public function queue($ts = null);
}
