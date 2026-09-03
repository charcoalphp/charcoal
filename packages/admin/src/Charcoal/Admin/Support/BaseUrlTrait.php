<?php

namespace Charcoal\Admin\Support;

use RuntimeException;
// From PSR-7
use Psr\Http\Message\UriInterface;

/**
 * URI Support Trait
 */
trait BaseUrlTrait
{
    /**
     * The base URI.
     *
     * @var UriInterface
     */
    protected $baseUrl;

    /**
     * The base admin URI.
     *
     * @var UriInterface
     */
    protected $adminUrl;

    /**
     * Set the base URI of the application.
     *
     * @see    \Charcoal\App\ServiceProvider\AppServiceProvider `$container['base-url']`
     * @param  UriInterface $uri The base URI.
     * @return self
     */
    protected function setBaseUrl(UriInterface $uri)
    {
        $this->baseUrl = $uri;
        return $this;
    }

    /**
     * Retrieve the base URI of the application.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the base URI is missing.
     * @return string|null
     */
    public function baseUrl($targetPath = null)
    {
        if (!isset($this->baseUrl)) {
            throw new RuntimeException(sprintf(
                'The base URI is not defined for [%s]',
                get_class($this)
            ));
        }

        if ($targetPath !== null) {
            return $this->createAbsoluteUrl($this->baseUrl, $targetPath);
        }

        return rtrim($this->baseUrl, '/') . '/';
    }

    /**
     * Set the URI of the administration-area.
     *
     * @see    \Charcoal\App\ServiceProvider\AdminServiceProvider `$container['admin/base-url']`
     * @param  UriInterface $uri The base URI.
     * @return self
     */
    protected function setAdminUrl(UriInterface $uri)
    {
        $this->adminUrl = $uri;
        return $this;
    }

    /**
     * Retrieve the URI of the administration-area.
     *
     * @param  mixed $targetPath Optional target path.
     * @throws RuntimeException If the admin URI is missing.
     * @return UriInterface|null
     */
    public function adminUrl($targetPath = null)
    {
        if (!isset($this->adminUrl)) {
            throw new RuntimeException(sprintf(
                'The Admin URI is not defined for [%s]',
                get_class($this)
            ));
        }

        if ($targetPath !== null) {
            return $this->createAbsoluteUrl($this->adminUrl, $targetPath);
        }

        return rtrim($this->adminUrl, '/') . '/';
    }

    /**
     * Determine if the given URI is relative.
     *
     * @param  string $uri A URI path to test.
     * @return boolean
     */
    protected function isRelativeUri($uri)
    {
        if ($uri && !parse_url($uri, PHP_URL_SCHEME)) {
            if (!in_array($uri[0], [ '/', '#', '?' ])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a redirect target is safe (same-origin or path-relative).
     *
     * Rejects scheme-based open redirects (`https://evil`, `javascript:`),
     * protocol-relative URLs (`//evil`), and backslash tricks.
     *
     * @param  mixed $url Candidate redirect URL.
     * @return boolean
     */
    public function isSafeRedirectUrl($url)
    {
        if (!is_string($url) || $url === '') {
            return false;
        }

        // Header / JS injection via control characters.
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        if (strpos($url, '\\') !== false) {
            return false;
        }

        // Protocol-relative or absolute with authority.
        if (preg_match('#^(?:[a-z][a-z0-9+\-.]*:)?//#i', $url)) {
            return $this->isSameOriginRedirectUrl($url);
        }

        // Schemes without "//" (javascript:, data:, etc.).
        if (preg_match('#^[a-z][a-z0-9+\-.]*:#i', $url)) {
            return false;
        }

        // Path-absolute, path-relative, query- or fragment-only.
        return true;
    }

    /**
     * Return $url when safe, otherwise $fallback (default: admin base URL).
     *
     * @param  mixed       $url      Candidate redirect URL.
     * @param  string|null $fallback Fallback when $url is unsafe. Null uses adminUrl().
     * @return string
     */
    public function sanitizeRedirectUrl($url, $fallback = null)
    {
        if ($this->isSafeRedirectUrl($url)) {
            return $url;
        }

        if ($fallback === null) {
            $fallback = (string)$this->adminUrl();
        }

        return $fallback;
    }

    /**
     * Whether an absolute or protocol-relative URL shares this app's origin.
     *
     * @param  string $url Absolute or protocol-relative URL.
     * @return boolean
     */
    protected function isSameOriginRedirectUrl($url)
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return false;
        }

        if (!isset($this->baseUrl)) {
            return false;
        }

        $baseHost = strtolower((string)$this->baseUrl->getHost());
        if ($baseHost === '') {
            // Relative application base: cannot validate off-site absolute URLs.
            return false;
        }

        if (strtolower($parts['host']) !== $baseHost) {
            return false;
        }

        if (isset($parts['scheme'])) {
            $scheme = strtolower($parts['scheme']);
            if (!in_array($scheme, [ 'http', 'https' ], true)) {
                return false;
            }

            $baseScheme = strtolower((string)$this->baseUrl->getScheme());
            if ($baseScheme !== '' && $scheme !== $baseScheme) {
                return false;
            }
        }

        $basePort = $this->baseUrl->getPort();
        if (isset($parts['port']) && $basePort !== null && (int)$parts['port'] !== (int)$basePort) {
            return false;
        }

        return true;
    }

    /**
     * Prepend the base URI to the given path.
     *
     * @param  UriInterface $basePath   The base path.
     * @param  string       $targetPath The target path.
     * @return UriInterface|string The absolute URI.
     */
    protected function createAbsoluteUrl(UriInterface $basePath, $targetPath)
    {
        $targetPath = strval($targetPath);
        if ($targetPath === '') {
            return $basePath->withPath('');
        } else {
            if ($this->isRelativeUri($targetPath)) {
                $parts = parse_url($targetPath);
                $path  = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
                $query = isset($parts['query']) ? $parts['query'] : '';
                $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';
                $targetPath = $basePath->withPath($path)->withQuery($query)->withFragment($hash);
            }
        }

        return $targetPath;
    }
}
