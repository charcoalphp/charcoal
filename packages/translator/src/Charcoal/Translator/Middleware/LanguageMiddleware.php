<?php

namespace Charcoal\Translator\Middleware;

// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-translator'
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\TranslatorAwareTrait;

/**
 * Class LanguageMiddleware
 */
class LanguageMiddleware
{
    use TranslatorAwareTrait;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @var string
     */
    private $browserLanguage;

    private array $excludedPath;

    private bool $usePath;

    /**
     * @var string
     */
    private $pathRegexp;

    private bool $useBrowser;

    private bool $useSession;

    /**
     * @var string[]
     */
    private array $sessionKey;

    private bool $useParams;

    /**
     * @var string[]
     */
    private array $paramKey;

    private bool $useHost;

    private array $hostMap;

    private bool $setLocale;

    /**
     * @param array $data The middleware options.
     */
    public function __construct(array $data)
    {
        $this->setTranslator($data['translator']);

        $data = array_replace($this->defaults(), $data);

        $this->defaultLanguage = $data['default_language'];
        $this->browserLanguage = $data['browser_language'];

        $this->usePath      = (bool) $data['use_path'];
        $this->excludedPath = (array)$data['excluded_path'];
        $this->pathRegexp   = $data['path_regexp'];

        $this->useParams    = (bool) $data['use_params'];
        $this->paramKey     = (array)$data['param_key'];

        $this->useSession   = (bool) $data['use_session'];
        $this->sessionKey   = (array)$data['session_key'];

        $this->useBrowser   = (bool) $data['use_browser'];

        $this->useHost      = (bool) $data['use_host'];
        $this->hostMap      = (array)$data['host_map'];

        $this->setLocale    = (bool) $data['set_locale'];
    }

    /**
     * Default middleware options.
     */
    public function defaults(): array
    {
        return [
            'default_language' => null,
            'browser_language' => null,

            'use_path'         => true,
            'excluded_path'    => [ '^/admin\b' ],
            'path_regexp'      => '^/([a-z]{2})\b',

            'use_params'       => false,
            'param_key'        => 'current_language',

            'use_session'      => true,
            'session_key'      => 'current_language',

            'use_browser'      => true,

            'use_host'         => false,
            'host_map'         => [],

            'set_locale'       => true
        ];
    }

    /**
     * @param  RequestInterface  $request  The PSR-7 HTTP request.
     * @param  ResponseInterface $response The PSR-7 HTTP response.
     * @param  callable          $next     The next middleware callable in the stack.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // Test if path is excluded from middleware.
        $uri  = $request->getUri();
        $path = $uri->getPath();
        foreach ($this->excludedPath as $excluded) {
            if (preg_match('@' . $excluded . '@', $path)) {
                return $next($request, $response);
            }
        }

        $language = $this->getLanguage($request);
        $this->setLanguage($language);

        return $next($request, $response);
    }

    /**
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return null|string
     */
    private function getLanguage(RequestInterface $request)
    {
        if ($this->useHost) {
            $lang = $this->getLanguageFromHost($request);
            if ($lang) {
                return $lang;
            }
        }

        if ($this->usePath) {
            $lang = $this->getLanguageFromPath($request);
            if ($lang !== '' && $lang !== '0') {
                return $lang;
            }
        }

        if ($this->useParams) {
            $lang = $this->getLanguageFromParams($request);
            if ($lang) {
                return $lang;
            }
        }

        if ($this->useSession) {
            $lang = $this->getLanguageFromSession();
            if ($lang) {
                return $lang;
            }
        }

        if ($this->useBrowser) {
            $lang = $this->getLanguageFromBrowser();
            if ($lang) {
                return $lang;
            }
        }

        return $this->defaultLanguage;
    }

    /**
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return string
     */
    private function getLanguageFromHost(RequestInterface $request): int|string
    {
        $uriHost = $request->getUri()->getHost();
        foreach ($this->hostMap as $lang => $host) {
            if (stripos($uriHost, (string) $host) !== false) {
                return $lang;
            }
        }

        return '';
    }

    /**
     * @param  RequestInterface $request The PSR-7 HTTP request.
     */
    private function getLanguageFromPath(RequestInterface $request): string
    {
        $path = $request->getRequestTarget();
        if (preg_match('@' . $this->pathRegexp . '@', $path, $matches)) {
            $lang = $matches[1];
        } else {
            return '';
        }

        if (in_array($lang, $this->translator()->availableLocales())) {
            return $lang;
        } else {
            return '';
        }
    }

    /**
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return string
     */
    private function getLanguageFromParams(RequestInterface $request)
    {
        if ($request instanceof ServerRequestInterface) {
            $locales = $this->translator()->availableLocales();
            $params  = $request->getQueryParams();
            foreach ($this->paramKey as $key) {
                if (isset($params[$key]) && in_array($params[$key], $locales)) {
                    return $params[$key];
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    private function getLanguageFromSession()
    {
        $locales = $this->translator()->availableLocales();
        foreach ($this->sessionKey as $key) {
            if (isset($_SESSION[$key]) && in_array($_SESSION[$key], $locales)) {
                return $_SESSION[$key];
            }
        }

        return '';
    }

    /**
     * @return mixed
     */
    private function getLanguageFromBrowser()
    {
        return $this->browserLanguage;
    }

    /**
     * @param  string $lang The language code to set.
     */
    private function setLanguage($lang): void
    {
        $this->translator()->setLocale($lang);

        if ($this->useSession) {
            foreach ($this->sessionKey as $key) {
                $_SESSION[$key] = $this->translator()->getLocale();
            }
        }

        if ($this->setLocale) {
            $this->setLocale($lang);
        }
    }

    /**
     * @param  string $lang The language code to set.
     */
    private function setLocale($lang): void
    {
        $translator = $this->translator();
        $available  = $translator->locales();
        $fallbacks  = $translator->getFallbackLocales();

        array_unshift($fallbacks, $lang);
        $fallbacks = array_unique($fallbacks);

        $locales = [];
        foreach ($fallbacks as $code) {
            if (isset($available[$code])) {
                $locale = $available[$code];
                if (isset($locale['locales'])) {
                    $choices = (array)$locale['locales'];
                    array_push($locales, ...$choices);
                } elseif (isset($locale['locale'])) {
                    $locales[] = $locale['locale'];
                }
            }
        }

        $locales = array_unique($locales);

        if ($locales !== []) {
            setlocale(LC_ALL, $locales);
        }
    }
}
