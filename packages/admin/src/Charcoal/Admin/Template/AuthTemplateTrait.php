<?php

namespace Charcoal\Admin\Template;

// From PSR-7
use Psr\Http\Message\RequestInterface;

/**
 *
 */
trait AuthTemplateTrait
{
    /**
     * @var string|null
     */
    private $csrfName;

    /**
     * @var string|null
     */
    private $csrfValue;

    /**
     * Reads the CSRF token pair attached to the request by the CSRF
     * middleware (`Charcoal\App\Middleware\CsrfMiddleware`, wrapping
     * {@see \Slim\Csrf\Guard}), for {@see self::csrfFields()} to render.
     *
     * Unlike a form whose action URL is arbitrary (a CMS page, say), an auth
     * form's GET-rendered page and its POST target are the same fixed route,
     * so the middleware itself both issues the token (on GET) and validates
     * it (on POST) — no separate token-issuance call is needed here.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return void
     */
    protected function setCsrfAttributesFromRequest(RequestInterface $request): void
    {
        $this->csrfName  = $request->getAttribute('csrf_name');
        $this->csrfValue = $request->getAttribute('csrf_value');
    }

    /**
     * @return string The hidden `<input>` markup carrying the CSRF token pair.
     */
    public function csrfFields(): string
    {
        if (!$this->csrfName || !$this->csrfValue) {
            return '';
        }

        return '<input type="hidden" name="csrf_name" value="' .
                htmlspecialchars($this->csrfName, ENT_QUOTES) .
                '"><input type="hidden" name="csrf_value" value="' .
                htmlspecialchars($this->csrfValue, ENT_QUOTES) .
                '">';
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the base URI is missing.
     * @return string|null
     */
    abstract public function baseUrl($targetPath = null);

    /**
     * Retrieve the URI of the administration-area.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the admin URI is missing.
     * @return UriInterface|null
     */
    abstract public function adminUrl($targetPath = null);

    /**
     * Retrieve the admin's configset.
     *
     * @param  string|null $key     Optional data key to retrieve from the configset.
     * @param  mixed|null  $default The default value to return if data key does not exist.
     * @return mixed|AdminConfig
     */
    abstract protected function adminConfig($key = null, $default = null);

    /**
     * @return string
     */
    public function urlLogin()
    {
        return $this->adminUrl('login');
    }

    /**
     * @return string
     */
    public function urlLostPassword()
    {
        return $this->adminUrl('account/lost-password');
    }

    /**
     * @return string
     */
    public function urlResetPassword()
    {
        return $this->adminUrl('account/reset-password');
    }

    /**
     * Get the "Back to website" label.
     *
     * @return string|boolean The button's label,
     *     TRUE to use the default label,
     *     or FALSE to disable the link.
     */
    public function returnToSiteLabel()
    {
        $label = $this->adminConfig('login.visit_site');
        if ($label === false) {
            return false;
        }

        if (empty($label) || $label === true) {
            $label = $this->translator()->translate('Back to website');
        } else {
            $label = $this->translator()->translate($label);
        }

        return '&larr; ' . $label;
    }

    /**
     * Get the background image, from admin config.
     *
     * @return string
     */
    public function backgroundImage()
    {
        $backdrop = $this->adminConfig('login.background_image');
        if (empty($backdrop)) {
            return '';
        }

        return $this->baseUrl($backdrop);
    }

    /**
     * Get the background video, from admin config.
     *
     * @return string
     */
    public function backgroundVideo()
    {
        $backdrop = $this->adminConfig('login.background_video');
        if (empty($backdrop)) {
            return '';
        }

        return $this->baseUrl($backdrop);
    }

    /**
     * @return string
     */
    public function avatarImage()
    {
        $logo = $this->adminConfig('login.logo') ?:
                $this->adminConfig('login_logo', 'assets/admin/images/avatar.jpg');

        if (empty($logo)) {
            return '';
        }

        return $this->baseUrl($logo);
    }
}
