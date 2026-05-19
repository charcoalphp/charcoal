<?php

namespace Charcoal\Admin\Template\Account;

// From PSR-7
use Psr\Http\Message\RequestInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;

/**
 * Lost Password Template
 *
 * Related: {@see \Charcoal\Admin\Template\Account\ResetPasswordTemplate Reset Password Template}
 */
class LostPasswordTemplate extends AdminTemplate
{
    use AuthTemplateTrait;

    /**
     * Determine if the password token is valid.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     */
    #[\Override]
    public function init(RequestInterface $request): bool
    {
        $translator = $this->translator();

        $notice = $request->getParam('notice');
        switch ($notice) {
            case 'invalidtoken':
                $message = $translator->translate('Your password reset token is invalid or expired.') . ' ' .
                           $translator->translate('Please request a new token below.');
                $this->addFeedback([
                    'level'       => 'error',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;

            case 'resetpass':
                $message = $translator->translate('Check your email for instructions to reset your password.');
                $this->addFeedback([
                    'level'       => 'notice',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;
        }

        return true;
    }

    #[\Override]
    public function authRequired(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function urlLostPasswordAction()
    {
        return $this->adminUrl('account/lost-password');
    }

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    #[\Override]
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Lost Password'));
        }

        return $this->title;
    }

    /**
     * Retrieve the parameters for the Google reCAPTCHA widget.
     *
     * @return string[]
     */
    #[\Override]
    public function recaptchaParameters(): array
    {
        $params = parent::recaptchaParameters();
        $params['tabindex'] = 2;

        if ($this->recaptchaInvisible() === true) {
            $params['callback'] = 'CharcoalCaptchaResetPassCallback';
        }

        return $params;
    }



    // Templating
    // =========================================================================
    /**
     * Determine if main & secondary menu should appear as mobile in a desktop resolution.
     */
    #[\Override]
    public function isFullscreenTemplate(): bool
    {
        return true;
    }
}
