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
     *
     * @var boolean $smtp
     */
    private $smtp = false;

    /**
     * The SMTP hostname.
     *
     * @var string $smtpHostname
     */
    private $smtpHostname;

    /**
     * The SMTP port.
     *
     * @var integer $smtpPort
     */
    private $smtpPort;

    /**
     * The SMTP security type.
     *
     * @var string $smtpSecurity
     */
    private $smtpSecurity = '';

    /**
     * Whether SMTP requires authentication.
     *
     * @var boolean $smtpAuth
     */
    private $smtpAuth;

    /**
     * The SMTP username.
     *
     * @var string $smtpUsername
     */
    private $smtpUsername;

    /**
     * The SMTP password.
     *
     * @var string $smtpPassword
     */
    private $smtpPassword;

    /**
     * The default sender's email address.
     *
     * @var string $defaultFrom
     */
    private $defaultFrom;

    /**
     * The default "Reply-To" email address.
     *
     * @var string $defaultReplyTo
     */
    private $defaultReplyTo;

    /**
     * Whether the email (open) should be tracked by default.
     *
     * @var boolean $defaultTrack
     */
    private $defaultTrackOpenEnabled;


    /**
     * Whether the email (links) should be tracked by default.
     *
     * @var boolean $defaultTrack
     */
    private $defaultTrackLinksEnabled;

    /**
     * Whether the email should be logged by default.
     *
     * @var boolean $defaultLog
     */
    private $defaultLogEnabled;

    /**
     * Default email configuration.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'smtp'             => false,

            'default_from'     => '',
            'default_reply_to' => '',

            'default_track_open_enabled'    => true,
            'default_track_links_enabled'   => true,
            'default_log_enabled'           => true
        ];
    }

    /**
     * Set whether SMTP should be used for sending the email.
     *
     * @param  boolean $smtp If the email should be sent using SMTP or not.
     * @throws InvalidArgumentException If the SMTP state is not a boolean.
     * @return self
     */
    public function setSmtp($smtp)
    {
        $this->smtp = !!$smtp;
        return $this;
    }

    /**
     * Determine if SMTP should be used.
     *
     * @return boolean
     */
    public function smtp()
    {
        return $this->smtp;
    }

    /**
     * Set the SMTP hostname to be used.
     *
     * @param  string $hostname The SMTP hostname.
     * @throws InvalidArgumentException If the SMTP hostname is not a string.
     * @return self
     */
    public function setSmtpHostname($hostname)
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
    public function smtpHostname()
    {
        return $this->smtpHostname;
    }

    /**
     * Set the SMTP port to be used.
     *
     * @param  integer $port The SMTP port.
     * @throws InvalidArgumentException If the SMTP port is not an integer.
     * @return self
     */
    public function setSmtpPort($port)
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
    public function smtpPort()
    {
        return $this->smtpPort;
    }

    /**
     * Set whether SMTP requires authentication.
     *
     * @param  boolean $auth The SMTP authentication flag (if auth is required).
     * @return self
     */
    public function setSmtpAuth($auth)
    {
        $this->smtpAuth = !!$auth;
        return $this;
    }

    /**
     * Determine if SMTP requires authentication.
     *
     * @return boolean
     */
    public function smtpAuth()
    {
        return $this->smtpAuth;
    }

    /**
     * Set the SMTP username to be used.
     *
     * @param  string $username The SMTP username, if using authentication.
     * @throws InvalidArgumentException If the SMTP username is not a string.
     * @return self
     */
    public function setSmtpUsername($username)
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
    public function smtpUsername()
    {
        return $this->smtpUsername;
    }

    /**
     * Set the SMTP password to be used.
     *
     * @param  string $password The SMTP password, if using authentication.
     * @throws InvalidArgumentException If the SMTP password is not a string.
     * @return self
     */
    public function setSmtpPassword($password)
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
    public function smtpPassword()
    {
        return $this->smtpPassword;
    }

    /**
     * Set the SMTP security type to be used.
     *
     * @param  string $security The SMTP security type (empty, "TLS", or "SSL").
     * @throws InvalidArgumentException If the security type is not valid (empty, "TLS", or "SSL").
     * @return self
     */
    public function setSmtpSecurity($security)
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
     *
     * @return string
     */
    public function smtpSecurity()
    {
        return $this->smtpSecurity;
    }

    /**
     * Set the default sender's email address.
     *
     * @param  string|array $email The default "From" email address.
     * @return self
     */
    public function setDefaultFrom($email)
    {
        $this->defaultFrom = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the sender email address.
     *
     * @return string
     */
    public function defaultFrom()
    {
        return $this->defaultFrom;
    }

    /**
     * Set the default "Reply-To" email address.
     *
     * @param  string|array $email The default "Reply-To" email address.
     * @return self
     */
    public function setDefaultReplyTo($email)
    {
        $this->defaultReplyTo = $this->parseEmail($email);
        return $this;
    }

    /**
     * Get the "Reply-To" email address.
     *
     * @return string
     */
    public function defaultReplyTo()
    {
        return $this->defaultReplyTo;
    }

    /**
     * Set whether the email sending should be logged by default.
     *
     * @param  boolean $log The default log flag.
     * @return self
     */
    public function setDefaultLogEnabled($log)
    {
        $this->defaultLogEnabled = !!$log;
        return $this;
    }

    /**
     * Determine if the email sending should be logged by default.
     *
     * @return boolean
     */
    public function defaultLogEnabled()
    {
        return $this->defaultLogEnabled;
    }

    /**
     * Set whether the email (open) should be tracked by default.
     *
     * @param  boolean $track The default track flag.
     * @return self
     */
    public function setDefaultTrackOpenEnabled($track)
    {
        $this->defaultTrackOpenEnabled = !!$track;
        return $this;
    }

    /**
     * Determine if the email (open) should be tracked by default.
     *
     * @return boolean
     */
    public function defaultTrackOpenEnabled()
    {
        return $this->defaultTrackOpenEnabled;
    }

    /**
     * Set whether the email links should be tracked by default.
     *
     * @param  boolean $track The default track flag.
     * @return self
     */
    public function setDefaultTrackLinksEnabled($track)
    {
        $this->defaultTrackLinksEnabled = !!$track;
        return $this;
    }

    /**
     * Determine if the email links should be tracked by default.
     *
     * @return boolean
     */
    public function defaultTrackLinksEnabled()
    {
        return $this->defaultTrackLinksEnabled;
    }
}
