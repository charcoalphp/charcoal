<?php

namespace Charcoal\Email;

// From `PHP`
use \InvalidArgumentException as InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig as AbstractConfig;

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
     * Whether the email should be tracked by default.
     *
     * @var boolean $defaultTrack
     */
    private $defaultTrack;

    /**
     * Whether the email should be logged by default.
     *
     * @var boolean $defaultLog
     */
    private $defaultLog;

    /**
     * Default email configuration.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'smtp'              => false,

            'default_from'      => '',
            'default_reply_to'  => '',

            'default_track'     => false,
            'default_log'       => true
        ];
    }

    /**
     * Set whether SMTP should be used for sending the email.
     *
     * @param  boolean $smtp If the email should be sent using SMTP or not.
     * @throws InvalidArgumentException If the SMTP state is not a boolean.
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @return EmailConfig Chainable
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
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailConfig Chainable
     */
    public function setDefaultFrom($email)
    {
        if (is_string($email)) {
            $this->defaultFrom = $email;
        } elseif (is_array($email)) {
            $this->defaultFrom = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Default sender email address must be an array or a string.'
            );
        }

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
     * @throws InvalidArgumentException If the email address is invalid.
     * @return EmailConfig Chainable
     */
    public function setDefaultReplyTo($email)
    {
        if (is_string($email)) {
            $this->defaultReplyTo = $email;
        } elseif (is_array($email)) {
            $this->defaultReplyTo = $this->emailFromArray($email);
        } else {
            throw new InvalidArgumentException(
                'Default reply-to email address must be an array or a string.'
            );
        }

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
     * Set whether the email should be logged by defaultd.
     *
     * @param  boolean $log The default log flag.
     * @return EmailConfig Chainable
     */
    public function setDefaultLog($log)
    {
        $this->defaultLog = !!$log;
        return $this;
    }

    /**
     * Determine if the email should be logged by default.
     *
     * @return boolean
     */
    public function defaultLog()
    {
        return $this->defaultLog;
    }

    /**
     * Set whether the email should be tracked by default.
     *
     * @param  boolean $track The default track flag.
     * @return EmailConfig Chainable
     */
    public function setDefaultTrack($track)
    {
        $this->defaultTrack = !!$track;
        return $this;
    }

    /**
     * Determine if the email should be tracked by default.
     *
     * @return boolean
     */
    public function defaultTrack()
    {
        return $this->defaultTrack;
    }
}
