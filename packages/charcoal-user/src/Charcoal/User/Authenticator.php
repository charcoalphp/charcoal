<?php

namespace Charcoal\User;

use InvalidArgumentException;

// From 'charcoal-object'
use Charcoal\Object\ContentInterface;

// From 'charcoal-user'
use Charcoal\User\Access\AuthenticatableInterface;
use Charcoal\User\UserInterface;

/**
 * User Authenticator
 */
class Authenticator extends AbstractAuthenticator
{
    /**
     * Log a user into the application.
     *
     * @param  AuthenticatableInterface $user     The authenticated user to log in.
     * @param  boolean                  $remember Whether to "remember" the user or not.
     * @return void
     */
    public function login(AuthenticatableInterface $user, $remember = false)
    {
        parent::login($user, $remember);

        $this->touchUserLogin($user);
    }

    /**
     * Validate the user authentication state is okay.
     *
     * For example, inactive users can not authenticate.
     *
     * @param  AuthenticatableInterface $user The user to validate.
     * @return boolean
     */
    public function validateAuthentication(AuthenticatableInterface $user)
    {
        if ($user instanceof ContentInterface) {
            if (!$user['active']) {
                return false;
            }
        }

        return parent::validateAuthentication($user);
    }

    /**
     * Updates the user's timestamp for their last log in.
     *
     * @param  AuthenticatableInterface $user   The user to update.
     * @param  boolean                  $update Whether to persist changes to storage.
     * @throws InvalidArgumentException If the user has no ID.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    public function touchUserLogin(AuthenticatableInterface $user, $update = true)
    {
        if (!($user instanceof UserInterface)) {
            return false;
        }

        if (!$user->getAuthId()) {
            throw new InvalidArgumentException(
                'Can not touch user: user has no ID'
            );
        }

        $userId = $user->getAuthId();

        if ($update && $userId) {
            $userClass = get_class($user);

            $this->logger->info(sprintf(
                'Updating last login fields for user "%s" (%s)',
                $userId,
                $userClass
            ));
        }

        $user['lastLoginDate'] = 'now';
        $user['lastLoginIp']   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        if ($update && $userId) {
            $result = $user->update([
                'last_login_ip',
                'last_login_date',
            ]);

            if ($result) {
                $this->logger->notice(sprintf(
                    'Last login fields were updated for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            } else {
                $this->logger->warning(sprintf(
                    'Last login fields failed to be updated for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            }
        }

        return $result;
    }

    /**
     * Updates the user's password hash.
     *
     * @param  AuthenticatableInterface $user     The user to update.
     * @param  string                   $password The plain-text password to hash.
     * @param  boolean                  $update   Whether to persist changes to storage.
     * @throws InvalidArgumentException If the password is invalid.
     * @return boolean Returns TRUE if the password was changed, or FALSE otherwise.
     */
    public function changeUserPassword(AuthenticatableInterface $user, $password, $update = true)
    {
        if (!($user instanceof UserInterface)) {
            return parent::changeUserPassword($user, $password);
        }

        if (!$this->validateAuthPassword($password)) {
            throw new InvalidArgumentException(
                'Can not change password: password is invalid'
            );
        }

        $userId = $user->getAuthId();

        if ($update && $userId) {
            $userClass = get_class($user);

            $this->logger->info(sprintf(
                '[Authenticator] Changing password for user "%s" (%s)',
                $userId,
                $userClass
            ));
        }

        $passwordKey = $user->getAuthPasswordKey();

        $user[$passwordKey]       = password_hash($password, PASSWORD_DEFAULT);
        $user['lastPasswordDate'] = 'now';
        $user['lastPasswordIp']   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

        if ($update && $userId) {
            $result = $user->update([
                $passwordKey,
                'last_password_date',
                'last_password_ip',
            ]);

            if ($result) {
                $this->logger->notice(sprintf(
                    '[Authenticator] Password was changed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            } else {
                $this->logger->warning(sprintf(
                    '[Authenticator] Password failed to be changed for user "%s" (%s)',
                    $userId,
                    $userClass
                ));
            }

            return $result;
        }

        return true;
    }
}
