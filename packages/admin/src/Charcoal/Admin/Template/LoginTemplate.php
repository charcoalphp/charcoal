<?php

namespace Charcoal\Admin\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Template\AuthTemplateTrait;

/**
 *
 */
class LoginTemplate extends AdminTemplate
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
            case 'resetpass':
                $message = $translator->translate('Check your email for instructions to reset your password.');
                $this->addFeedback([
                    'level'       => 'notice',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;

            case 'newpass':
                $message = $translator->translate('Log in with your new password.');
                $this->addFeedback([
                    'level'       => 'notice',
                    'message'     => $message,
                    'dismissible' => false
                ]);
                break;
        }

        return true;
    }

    /**
     * Authentication is obviously never required for the login page.
     */
    #[\Override]
    protected function authRequired(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function urlLoginAction()
    {
        return $this->adminUrl('login');
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
            $this->setTitle($this->translator()->translation('auth.login.title'));
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
        $params['tabindex'] = 3;

        if ($this->recaptchaInvisible() === true) {
            $params['callback'] = 'CharcoalCaptchaLoginCallback';
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
