<?php

namespace Charcoal\App\Middleware;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * The security headers middleware adds HTTP response headers that instruct
 * browsers to enable client-side security mechanisms (XSS mitigation,
 * clickjacking protection, transport security, feature restriction, etc.).
 *
 * It is designed to be added as the outermost middleware (i.e. added last in
 * the `middlewares` configuration) so that the headers are applied to every
 * response, including cached and error responses.
 *
 * The headers are also sent to PHP's header list before the remaining
 * middleware stack runs. This ensures they are present on responses that
 * bypass PSR-7 (and therefore this middleware's return value) by writing
 * headers and calling `exit`, such as Charcoal's legacy admin authentication
 * redirects and elFinder's connector. This behaviour can be disabled with the
 * `use_native_headers` option.
 *
 * Configuration example:
 *
 * ```json
 * {
 *     "middlewares": {
 *         "charcoal/app/middleware/security-headers": {
 *             "active": true,
 *             "headers": {
 *                 "Permissions-Policy": "camera=(), microphone=(), geolocation=()"
 *             },
 *             "hsts": {
 *                 "enabled": true,
 *                 "max_age": 15552000,
 *                 "include_subdomains": false
 *             },
 *             "csp": {
 *                 "enabled": "report-only",
 *                 "directives": {
 *                     "default-src": [ "'self'" ],
 *                     "object-src": [ "'none'" ]
 *                 }
 *             }
 *         }
 *     }
 * }
 * ```
 *
 * Per-path overrides are possible through the `profiles` option. Profiles are
 * keyed by a URI path regular expression (without delimiters) and are applied
 * in configuration order; the first matching profile wins. Profile values are
 * merged over the global configuration.
 */
class SecurityHeadersMiddleware
{
    /**
     * Headers applied to every response.
     *
     * A `null`, `false` or empty value disables a header.
     *
     * @var array<string, string|string[]|null>
     */
    private $headers;

    /**
     * HTTP Strict Transport Security options.
     *
     * @var array
     */
    private $hsts;

    /**
     * Content Security Policy options.
     *
     * @var array
     */
    private $csp;

    /**
     * Clear-Site-Data options.
     *
     * @var array
     */
    private $clearSiteData;

    /**
     * URI path patterns for which no headers should be added.
     *
     * @var string[]
     */
    private $excludedPath;

    /**
     * Per-path configuration overrides.
     *
     * @var array<string, array>
     */
    private $profiles;

    /**
     * Whether to send the headers to PHP's native header list before the
     * middleware stack runs.
     *
     * @var boolean
     */
    private $useNativeHeaders;

    /**
     * @param array $data Constructor dependencies and options.
     */
    public function __construct(array $data)
    {
        $data = array_replace_recursive($this->defaults(), $data);

        $this->headers = (array)$data['headers'];
        $this->hsts = (array)$data['hsts'];
        $this->csp = (array)$data['csp'];
        $this->clearSiteData = (array)$data['clear_site_data'];
        $this->excludedPath = (array)$data['excluded_path'];
        $this->profiles = (array)$data['profiles'];
        $this->useNativeHeaders = (bool)$data['use_native_headers'];
    }

    /**
     * Default middleware options.
     *
     * Only the headers that are safe for virtually every application are
     * enabled by default. Stricter headers (Permissions-Policy, COOP, CORP,
     * CSP) should be enabled explicitly by the application.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'headers' => [
                'X-Content-Type-Options'            => 'nosniff',
                'X-Frame-Options'                   => 'SAMEORIGIN',
                'Referrer-Policy'                   => 'strict-origin-when-cross-origin',
                'X-Permitted-Cross-Domain-Policies' => 'none',
            ],
            'hsts' => [
                'enabled'            => true,
                'max_age'            => 15552000, // 180 days
                'include_subdomains' => false,
                'preload'            => false,
                'only_secure'        => true,
            ],
            'csp' => [
                'enabled'    => false,
                'directives' => [],
                'report_uri' => null,
            ],
            'clear_site_data' => [
                'paths' => [],
                'value' => '"cache", "cookies", "storage", "executionContexts"',
            ],
            'excluded_path' => [],
            'profiles'      => [],
            'use_native_headers' => true,
        ];
    }

    /**
     * Apply the configured security headers to the response.
     *
     * The headers are sent to PHP's native header list before the middleware
     * stack runs, then applied to the PSR-7 response. The former covers legacy
     * code paths that write headers and call `exit`; the latter lets Slim send
     * the final response as usual.
     *
     * @param  RequestInterface  $request  The PSR-7 HTTP request.
     * @param  ResponseInterface $response The PSR-7 HTTP response.
     * @param  callable          $next     The next middleware callable in the stack.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $headers = $this->buildHeaders($request);

        if ($this->useNativeHeaders) {
            $this->sendNativeHeaders($headers);
        }

        $response = $next($request, $response);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /**
     * Build the security headers for the given request.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return array<string, string|string[]>
     */
    private function buildHeaders(RequestInterface $request): array
    {
        $path = $request->getUri()->getPath();
        if ($path === '') {
            $path = '/';
        }

        if ($this->matchesPath($path, $this->excludedPath)) {
            return [];
        }

        $config = $this->configForPath($path);
        $headers = [];

        foreach ($config['headers'] as $name => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }

            $headers[$name] = $value;
        }

        if (!empty($config['hsts']['enabled'])) {
            if (empty($config['hsts']['only_secure']) || $this->isSecureRequest($request)) {
                $headers['Strict-Transport-Security'] = $this->buildHsts($config['hsts']);
            }
        }

        $csp = $config['csp'];
        if (!empty($csp['enabled']) && !empty($csp['directives'])) {
            $header = ($csp['enabled'] === 'report-only')
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $headers[$header] = $this->buildCsp($csp);
        }

        if (
            !empty($config['clear_site_data']['paths'])
            && $this->matchesPath($path, $config['clear_site_data']['paths'])
        ) {
            $headers['Clear-Site-Data'] = $config['clear_site_data']['value'];
        }

        return $headers;
    }

    /**
     * Send headers to PHP's native header list.
     *
     * This is a no-op in CLI mode or once output has started.
     *
     * @param  array<string, string|string[]> $headers Header names and values.
     * @return void
     */
    private function sendNativeHeaders(array $headers): void
    {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }

        foreach ($headers as $name => $value) {
            if (is_array($value)) {
                $replace = true;
                foreach ($value as $item) {
                    header($name . ': ' . $item, $replace);
                    $replace = false;
                }
                continue;
            }

            header($name . ': ' . $value, true);
        }
    }

    /**
     * Retrieve the configuration for a given URI path.
     *
     * @param  string $path The request path.
     * @return array
     */
    private function configForPath(string $path): array
    {
        $config = [
            'headers'         => $this->headers,
            'hsts'            => $this->hsts,
            'csp'             => $this->csp,
            'clear_site_data' => $this->clearSiteData,
        ];

        foreach ($this->profiles as $pattern => $profile) {
            if (!is_array($profile) || !$this->matchesPath($path, [ $pattern ])) {
                continue;
            }

            foreach ($profile as $key => $value) {
                if (is_array($value) && isset($config[$key]) && is_array($config[$key])) {
                    // Merge one level deep so a profile can override only the
                    // options it cares about (and replace list values wholesale).
                    $config[$key] = array_replace($config[$key], $value);
                } else {
                    $config[$key] = $value;
                }
            }

            // A profile applies only once; the first match wins.
            break;
        }

        return $config;
    }

    /**
     * Build the Strict-Transport-Security header value.
     *
     * @param  array $hsts The HSTS options.
     * @return string
     */
    private function buildHsts(array $hsts): string
    {
        $value = 'max-age=' . (int)$hsts['max_age'];

        if (!empty($hsts['include_subdomains'])) {
            $value .= '; includeSubDomains';
        }

        if (!empty($hsts['preload'])) {
            $value .= '; preload';
        }

        return $value;
    }

    /**
     * Build the Content-Security-Policy header value.
     *
     * A directive set to `true` is emitted without a value (e.g.
     * `upgrade-insecure-requests`). A directive set to `false`, `null` or an
     * empty list is omitted.
     *
     * @param  array $csp The CSP options.
     * @return string
     */
    private function buildCsp(array $csp): string
    {
        $parts = [];

        foreach ($csp['directives'] as $directive => $sources) {
            if ($sources === true) {
                $parts[] = $directive;
                continue;
            }

            if ($sources === false || $sources === null) {
                continue;
            }

            $sources = array_filter((array)$sources, function ($source) {
                return $source !== null && $source !== false && $source !== '';
            });

            if (!$sources) {
                continue;
            }

            $parts[] = $directive . ' ' . implode(' ', array_map('strval', $sources));
        }

        if (!empty($csp['report_uri'])) {
            $parts[] = 'report-uri ' . $csp['report_uri'];
        }

        return implode('; ', $parts);
    }

    /**
     * Determine whether the request was served over HTTPS.
     *
     * Also checks the `X-Forwarded-Proto` header, since most production
     * deployments terminate TLS in front of the application.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    private function isSecureRequest(RequestInterface $request): bool
    {
        if ($request->getUri()->getScheme() === 'https') {
            return true;
        }

        $forwardedProto = $request->getHeaderLine('X-Forwarded-Proto');
        if ($forwardedProto !== '') {
            $protocols = explode(',', $forwardedProto);
            return strtolower(trim($protocols[0])) === 'https';
        }

        return false;
    }

    /**
     * Determine whether a path matches one of the given patterns.
     *
     * Patterns are regular expressions without delimiters, as used elsewhere
     * in Charcoal middleware configuration (e.g. the cache middleware).
     *
     * @param  string   $path     The request path.
     * @param  string[] $patterns The path patterns.
     * @return boolean
     */
    private function matchesPath(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === '*') {
                return true;
            }

            if (!is_string($pattern) || $pattern === '') {
                continue;
            }

            if (preg_match('@' . str_replace('@', '\@', $pattern) . '@', $path)) {
                return true;
            }
        }

        return false;
    }
}
