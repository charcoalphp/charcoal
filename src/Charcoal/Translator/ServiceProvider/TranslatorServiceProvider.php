<?php

namespace Charcoal\Translator\ServiceProvider;

// From Pimple
use Pimple\Container;
use Pimple\ServiceProviderInterface;

// From 'symfony/translation'
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\CsvFileLoader;
use Symfony\Component\Translation\Loader\IcuDatFileLoader;
use Symfony\Component\Translation\Loader\IcuResFileLoader;
use Symfony\Component\Translation\Loader\IniFileLoader;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\QtFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageSelector;

// From `charcoal-translator`
use Charcoal\Translator\LocalesConfig;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;
use Charcoal\Translator\TranslatorConfig;
use Charcoal\Translator\Middleware\LanguageMiddleware;

/**
 * Translation Service Provider
 *
 * Provides a service for translating your application into different languages,
 * and manage the target locale of a Charcoal application.
 */
class TranslatorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    public function register(Container$container)
    {
        $this->registerLocales($container);
        $this->registerTranslator($container);
        $this->registerMiddleware($container);
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    private function registerLocales(Container $container)
    {
        /**
         * @param  Container $container Pimple DI container.
         * @return LanguageConfig
         */
        $container['locales/config'] = function(Container $container) {
            $config = isset($container['config']) ? $container['config'] : [];
            $localesConfig = isset($config['locales']) ? $config['locales'] : null;
            return new LocalesConfig($localesConfig);
        };

        /**
         * Retrieve the list of language codes (locale ident) available.
         *
         * @param  Container $container Pimple DI container.
         * @return string[]
         */
        $container['locales/available-languages'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return array_keys($localesConfig['languages']);
        };

        /**
         * Retrieve the list of locales (as configuration structure) available.
         *
         * @param  Container $container Pimple DI container.
         * @return array
         */
        $container['locales/languages'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return $localesConfig['languages'];
        };

        /**
         * @param  Container $container Pimple DI container.
         * @return string
         */
        $container['locales/default-language'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            if (isset($localesConfig['auto_detect']) && $localesConfig['auto_detect']) {
                if ($container['locales/browser-language'] !== null) {
                    return $container['locales/browser-language'];
                }
            }
            return $localesConfig['default_language'];
        };

        /**
         * @param  Container $container Pimple DI container.
         * @return string|null
         */
        $container['locales/browser-language'] = function(Container $container) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return null;
            }
            $availableLanguages = $container['locales/available-languages'];
            $acceptedLanguages  = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptedLanguages as $acceptedLang) {
                $lang = explode(';', $acceptedLang);
                if (in_array($lang[0], $availableLanguages)) {
                    return $lang[0];
                }
            }
            return null;
        };

        /**
         * @param  Container $container Pimple DI container.
         * @return array
         */
        $container['locales/fallback-languages'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return $localesConfig['fallback_languages'];
        };

        /**
         * @param  Container $container Pimple DI container.
         * @return LocalesManager
         */
        $container['locales/manager'] = function (Container $container) {
            return new LocalesManager([
                'locales'             => $container['locales/languages'],
                'default_language'    => $container['locales/default-language'],
                'fallback_languages'  => $container['locales/fallback-languages']
            ]);
        };
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    private function registerTranslator(Container $container)
    {
        /**
         * @param  Container $container Pimple DI container.
         * @return TranslatorConfig
         */
        $container['translator/config'] = function (Container $container) {
            $config = isset($container['config']) ? $container['config'] : [];
            $translatorConfig = isset($config['translator']) ? $config['translator'] : null;
            return new TranslatorConfig($translatorConfig);
        };

        /**
         * @param  Container $container Pimple DI container.
         * @return array
         */
        $container['translator/translations'] = function (Container $container) {
            $translatorConfig = $container['translator/config'];
            return $translatorConfig['translations'];
        };

        /**
         * @return MessageSelector
         */
        $container['translator/message-selector'] = function () {
            return new MessageSelector();
        };

        /**
         * @todo   Improve file loader with a map of file formats.
         * @param  Container $container Pimple DI container.
         * @return Translator
         */
        $container['translator'] = function (Container $container) {
            $translatorConfig = $container['translator/config'];
            $translator = new Translator([
                'manager'           => $container['locales/manager'],
                'message_selector'  => $container['translator/message-selector'],
                'cache_dir'         => $translatorConfig['cache_dir'],
                'debug'             => $translatorConfig['debug']
            ]);

            $translator->setFallbackLocales($container['locales/fallback-languages']);

            $translator->addLoader('array', $container['translator/loader/array']);

            foreach ($translatorConfig['loaders'] as $loader) {
                $translator->addLoader($loader, $container['translator/loader/file/'.$loader]);
                foreach ($translatorConfig['paths'] as $path) {
                    $path = realpath($container['config']['base_path'].$path);
                    if ($path === false) {
                        continue;
                    }
                    $files = glob($path.'/*.'.$loader);
                    foreach ($files as $f) {
                        $names = explode('.', basename($f));
                        if (count($names) < 3) {
                            continue;
                        }
                        $lang = $names[1];
                        $domain = $names[0];
                        $translator->addResource($loader, $f, $lang, $domain);
                    }
                }
            }

            foreach ($container['translator/translations'] as $domain => $data) {
                foreach ($data as $locale => $messages) {
                    $translator->addResource('array', $messages, $locale, $domain);
                }
            }

            return $translator;
        };

        /**
         * @return ArrayLoader
         */
        $container['translator/loader/array'] = function() {
            return new ArrayLoader();
        };

        /**
         * @return CsvFileLoader
         */
        $container['translator/loader/file/csv'] = function() {
            return new CsvFileLoader();
        };

        /**
         * @return IcuDatFileLoader
         */
        $container['translator/loader/file/dat'] = function() {
            return new IcuDatFileLoader();
        };

        /**
         * @return IcuResFileLoader
         */
        $container['translator/loader/file/res'] = function() {
            return new IcuResFileLoader();
        };

        /**
         * @return IniFileLoader
         */
        $container['translator/loader/file/ini'] = function() {
            return new IniFileLoader();
        };

        /**
         * @return JsonFileLoader
         */
        $container['translator/loader/file/json'] = function() {
            return new JsonFileLoader();
        };

        /**
         * @return MoFileLoader
         */
        $container['translator/loader/file/mo'] = function() {
            return new MoFileLoader();
        };

        /**
         * @return PhpFileLoader
         */
        $container['translator/loader/file/php'] = function() {
            return new PhpFileLoader();
        };

        /**
         * @return PoFileLoader
         */
        $container['translator/loader/file/po'] = function() {
            return new PoFileLoader();
        };

        /**
         * @return QtFileLoader
         */
        $container['translator/loader/file/qt'] = function() {
            return new QtFileLoader();
        };

        /**
         * @return XliffFileLoader
         */
        $container['translator/loader/file/xliff'] = function() {
            return new XliffFileLoader();
        };

        /**
         * @return YamlFileLoader
         */
        $container['translator/loader/file/yaml'] = function() {
            return new YamlFileLoader();
        };
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    private function registerMiddleware(Container $container)
    {
        /**
         * @param Container $container
         * @return LanguageMiddleware
         */
        $container['middlewares/charcoal/translator/middleware/language'] = function (Container $container) {
            $middlewareConfig = $container['config']['middlewares']['charcoal/translator/middleware/language'];
            $middlewareConfig = array_replace(
                [
                    'default_language'  => $container['translator']->getLocale()
                ],
                $middlewareConfig,
                [
                    'translator'        => $container['translator'],
                    'browser_language'  => $container['locales/browser-language']
                ]
            );
            return new LanguageMiddleware($middlewareConfig);
        };
    }
}
