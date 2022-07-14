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

    /**
     * @var array
     */
    private $excludedPath;

    /**
     * @var boolean
     */
    private $usePath;

    /**
     * @var string
     */
    private $pathRegexp;

    /**
     * @var boolean
     */
    private $useBrowser;

    /**
     * @var boolean
     */
    private $useSession;

    /**
     * @var string[]
     */
    private $sessionKey;

    /**
     * @var boolean
     */
    private $useParams;

    /**
     * @var string[]
     */
    private $paramKey;

    /**
     * @var boolean
     */
    private $useHost;

    /**
     * @var array
     */
    private $hostMap;

    /**
     * @var boolean
     */
    private $setLocale;

    /**
     * @param array $data The middleware options.
     */
    public function __construct(array $data)
    {
        $this->setTranslator($data['translator']);

        $data = array_replace($this->defaults(), $data);

        $this->defaultLanguage = $data['default_language'];
        $this->browserLanguage = $data['browser_language'];

        $this->usePath      = !!$data['use_path'];
        $this->excludedPath = (array)$data['excluded_path'];
        $this->pathRegexp   = $data['path_regexp'];

        $this->useParams    = !!$data['use_params'];
        $this->paramKey     = (array)$data['param_key'];

        $this->useSession   = !!$data['use_session'];
        $this->sessionKey   = (array)$data['session_key'];

        $this->useBrowser   = !!$data['use_browser'];

        $this->useHost      = !!$data['use_host'];
        $this->hostMap      = (array)$data['host_map'];

        $this->setLocale    = !!$data['set_locale'];
    }

    /**
     * Default middleware options.
     *
     * @return array
     */
    public function defaults()
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
        if ($this->useHost === true) {
            $lang = $this->getLanguageFromHost($request);
            if ($lang) {
                return $lang;
            }
        }

        if ($this->usePath === true) {
            $lang = $this->getLanguageFromPath($request);
            if ($lang) {
                return $lang;
            }
        }

        if ($this->useParams === true) {
            $lang = $this->getLanguageFromParams($request);
            if ($lang) {
                return $lang;
            }
        }

        if ($this->useSession === true) {
            $lang = $this->getLanguageFromSession();
            if ($lang) {
                return $lang;
            }
        }

        if ($this->useBrowser === true) {
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
    private function getLanguageFromHost(RequestInterface $request)
    {
        $uriHost = $request->getUri()->getHost();
        foreach ($this->hostMap as $lang => $host) {
            if (stripos($uriHost, $host) !== false) {
                return $lang;
            }
        }

        return '';
    }

    /**
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return string
     */
    private function getLanguageFromPath(RequestInterface $request)
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
     * @return void
     */
    private function setLanguage($lang)
    {
        $this->translator()->setLocale($lang);

        if ($this->useSession === true) {
            foreach ($this->sessionKey as $key) {
                $_SESSION[$key] = $this->translator()->getLocale();
            }
        }

        if ($this->setLocale === true) {
            $this->setLocale($lang);
        }
    }

    /**
     * @param  string $lang The language code to set.
     * @return void
     */
    private function setLocale($lang)
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
                    array_push($locales, $locale['locale']);
                }
            }
        }

        $locales = array_unique($locales);

        if (!empty($locales)) {
            setlocale(LC_ALL, $locales);
        }
    }
}
