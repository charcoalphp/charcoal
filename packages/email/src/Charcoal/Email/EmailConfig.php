<?php

declare(strict_types=1);

namespace Charcoal\Email;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Config\AbstractConfig;

/**
 * Email configuration.
 */
class EmailConfig extends AbstractConfig
{
    use EmailAwareTrait;

    /**
     * Whether SMTP should be used.
     */
    private bool $smtp = false;

    /**
     * The SMTP hostname.
     */
    private ?string $smtpHostname = null;

    /**
     * The SMTP port.
     */
    private ?int $smtpPort = null;

    /**
     * The SMTP security type.
     */
    private string $smtpSecurity = '';

    /**
     * Whether SMTP requires authentication.
     */
    private ?bool $smtpAuth = null;

    /**
     * The SMTP username.
     */
    private ?string $smtpUsername = null;

    /**
     * The SMTP password.
     */
    private ?string $smtpPassword = null;

    /**
     * The default sender's email address.
     */
    private ?string $defaultFrom = null;

    /**
     * The default "Reply-To" email address.
     */
    private ?string $defaultReplyTo = null;

    /**
     * Whether the email (open) should be tracked by default.
     */
    private ?bool $defaultTrackOpenEnabled = null;


    /**
     * Whether the email (links) should be tracked by default.
     */
    private ?bool $defaultTrackLinksEnabled = null;

    /**
     * Whether the email should be logged by default.
     */
    private ?bool $defaultLogEnabled = null;

    /**
     * Default email configuration.
     */
    #[\Override]
    public function defaults(): array
    {
        return [
            'smtp'             => false,

            'default_from'     => '',
            'default_reply_to' => '',

            'default_track_open_enabled'    => false,
            'default_track_links_enabled'   => false,
            'default_log_enabled'           => true
        ];
    }

    /**
     * Set whether SMTP should be used for sending the email.
     *
     * @param  boolean $smtp If the email should be sent using SMTP or not.
     * @throws InvalidArgumentException If the SMTP state is not a boolean.
     */
    public function setSmtp($smtp): static
    {
        $this->smtp = (bool) $smtp;
        return $this;
    }

    /**
     * Determine if SMTP should be used.
     */
    public function smtp(): bool
    {
        return $this->smtp;
    }

    /**
     * Set the SMTP hostname to be used.
     *
     * @param  string $hostname The SMTP hostname.
     * @throws InvalidArgumentException If the SMTP hostname is not a string.
     */
    public function setSmtpHostname($hostname): static
    {
        if (!is_string($hostname)) {
            throw new InvalidArgumentException(
                'SMTP Hostname must be a string.'
            );
        }

        $this->smtpHostname = $hostname;

        return $this;
    }

    /**
     * Get the SMTP hostname.
     *
     * @return string
     */
    public function smtpHostname(): ?string
    {
        return $this->smtpHostname;
    }

    /**
     * Set the SMTP port to be used.
     *
     * @param  integer $port The SMTP port.
     * @throws InvalidArgumentException If the SMTP port is not an integer.
     */
    public function setSmtpPort($port): static
    {
        if (!is_int($port)) {
            throw new InvalidArgumentException(
                'SMTP Port must be an integer.'
            );
        }

        $this->smtpPort = $port;

        return $this;
    }

    /**
     * Get the SMTP port.
     *
     * @return integer
     */
    public function smtpPort(): ?int
    {
        return $this->smtpPort;
    }

    /**
     * Set whether SMTP requires authentication.
     *
     * @param  boolean $auth The SMTP authentication flag (if auth is required).
     */
    public function setSmtpAuth($auth): static
    {
        $this->smtpAuth = (bool) $auth;
        return $this;
    }

    /**
     * Determine if SMTP requires authentication.
     *
     * @return boolean
     */
    public function smtpAuth(): ?bool
    {
        return $this->smtpAuth;
    }

    /**
     * Set the SMTP username to be used.
     *
     * @param  string $username The SMTP username, if using authentication.
     * @throws InvalidArgumentException If the SMTP username is not a string.
     */
    public function setSmtpUsername($username): static
    {
        if (!is_string($username)) {
            throw new InvalidArgumentException(
                'SMTP Username must be a string.'
            );
        }

        $this->smtpUsername = $username;

        return $this;
    }

    /**
     * Get the SMTP username.
     *
     * @return string
     */
    public function smtpUsername(): ?string
    {
        return $this->smtpUsername;
    }

    /**
     * Set the SMTP password to be used.
     *
     * @param  string $password The SMTP password, if using authentication.
     * @throws InvalidArgumentException If the SMTP password is not a string.
     */
    public function setSmtpPassword($password): static
    {
        if (!is_string($password)) {
            throw new InvalidArgumentException(
                'SMTP Password must be a string.'
            );
        }

        $this->smtpPassword = $password;

        return $this;
    }

    /**
     * Get the SMTP password.
     *
     * @return string
     */
    public function smtpPassword(): ?string
    {
        return $this->smtpPassword;
    }

    /**
     * Set the SMTP security type to be used.
     *
     * @param  string $security The SMTP security type (empty, "TLS", or "SSL").
     * @throws InvalidArgumentException If the security type is not valid (empty, "TLS", or "SSL").
     */
    public function setSmtpSecurity($security): static
    {
        $security = strtoupper($security);
        $validSecurity = [ '', 'TLS', 'SSL' ];

        if (!in_array($security, $validSecurity)) {
            throw new InvalidArgumentException(
                'SMTP Security is not valid. Must be "", "TLS" or "SSL".'
            );
        }

        $this->smtpSecurity = $security;

        return $this;
    }

    /**
     * Get the SMTP security type.
     */
    public function smtpSecurity(): string
    {
        return $this->smtpSecurity;
    }

    /**
     * Set the default sender's email address.
     *
     * @param  string|array $email The default "From" email address.
     */
    public function setDefaultFrom($email): static
    {
        $this->defaultFrom = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the sender email address.
     *
     * @return string
     */
    public function defaultFrom(): ?string
    {
        return $this->defaultFrom;
    }

    /**
     * Set the default "Reply-To" email address.
     *
     * @param  string|array $email The default "Reply-To" email address.
     */
    public function setDefaultReplyTo($email): static
    {
        $this->defaultReplyTo = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the "Reply-To" email address.
     *
     * @return string
     */
    public function defaultReplyTo(): ?string
    {
        return $this->defaultReplyTo;
    }

    /**
     * Set whether the email sending should be logged by default.
     *
     * @param  boolean $log The default log flag.
     */
    public function setDefaultLogEnabled($log): static
    {
        $this->defaultLogEnabled = (bool) $log;
        return $this;
    }

    /**
     * Determine if the email sending should be logged by default.
     *
     * @return boolean
     */
    public function defaultLogEnabled(): ?bool
    {
        return $this->defaultLogEnabled;
    }

    /**
     * Set whether the email (open) should be tracked by default.
     *
     * @param  boolean $track The default track flag.
     */
    public function setDefaultTrackOpenEnabled($track): static
    {
        $this->defaultTrackOpenEnabled = (bool) $track;
        return $this;
    }

    /**
     * Determine if the email (open) should be tracked by default.
     *
     * @return boolean
     */
    public function defaultTrackOpenEnabled(): ?bool
    {
        return $this->defaultTrackOpenEnabled;
    }

    /**
     * Set whether the email links should be tracked by default.
     *
     * @param  boolean $track The default track flag.
     */
    public function setDefaultTrackLinksEnabled($track): static
    {
        $this->defaultTrackLinksEnabled = (bool) $track;
        return $this;
    }

    /**
     * Determine if the email links should be tracked by default.
     *
     * @return boolean
     */
    public function defaultTrackLinksEnabled(): ?bool
    {
        return $this->defaultTrackLinksEnabled;
    }
}
