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
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\MessageSelector;
// From 'charcoal-translator'
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
    public function register(Container $container)
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
         * Instance of the Locales Configset.
         *
         * @param  Container $container Pimple DI container.
         * @return LocalesConfig
         */
        $container['locales/config'] = function (Container $container) {
            $appConfig     = isset($container['config']) ? $container['config'] : [];
            $localesConfig = isset($appConfig['locales']) ? $appConfig['locales'] : null;
            return new LocalesConfig($localesConfig);
        };

        /**
         * Default language of the application, optionally the navigator's preferred language.
         *
         * @param  Container $container Pimple DI container.
         * @return string|null
         */
        $container['locales/default-language'] = function (Container $container) {
            $localesConfig = $container['locales/config'];
            if (isset($localesConfig['auto_detect']) && $localesConfig['auto_detect']) {
                if ($container['locales/browser-language'] !== null) {
                    return $container['locales/browser-language'];
                }
            }
            return $localesConfig['default_language'];
        };

        /**
         * Accepted language from the navigator.
         *
         * Example with Accept-Language "zh-Hant-HK, fr-CH, fr;q=0.9, en;q=0.7":
         *
         * 1. zh-Hant-HK
         * 2. fr-CH
         * 3. fr
         * 4. en
         *
         * @param  Container $container Pimple DI container.
         * @return string|null
         */
        $container['locales/browser-language'] = function (Container $container) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return null;
            }

            /**
             * Using data from configset instead of LocalesManager
             * since the latter might need the browser language
             * as the default language.
             */
            $localesConfig    = $container['locales/config'];
            $supportedLocales = array_filter($localesConfig['languages'], function ($locale) {
                return !(isset($locale['active']) && !$locale['active']);
            });

            $acceptableLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptableLanguages as $acceptedLang) {
                $lang = explode(';', $acceptedLang);
                $lang = trim($lang[0]);
                if (isset($supportedLocales[$lang])) {
                    return $lang;
                }
            }

            return null;
        };

        /**
         * List of fallback language codes for the translator.
         *
         * @todo   Use filtered "fallback_languages" from LocalesManager
         * @param  Container $container Pimple DI container.
         * @return string[]
         */
        $container['locales/fallback-languages'] = function (Container $container) {
            $localesConfig = $container['locales/config'];
            return $localesConfig['fallback_languages'];
        };

        /**
         * List of language codes (locale ident) from the available locales.
         *
         * @param  Container $container Pimple DI container.
         * @return string[]
         */
        $container['locales/available-languages'] = function (Container $container) {
            $manager = $container['locales/manager'];
            return $manager->availableLocales();
        };

        /**
         * List of available locales (as configuration structures) of the application.
         *
         * @param  Container $container Pimple DI container.
         * @return array
         */
        $container['locales/languages'] = function (Container $container) {
            $manager = $container['locales/manager'];
            return $manager->locales();
        };

        /**
         * Instance of the Locales Manager.
         *
         * @todo   Filter "fallback_languages"
         * @param  Container $container Pimple DI container.
         * @return LocalesManager
         */
        $container['locales/manager'] = function (Container $container) {
            $localesConfig = $container['locales/config'];
            return new LocalesManager([
                'locales'          => $localesConfig['languages'],
                'default_language' => $container['locales/default-language'],
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
         * Instance of the Translator Configset.
         *
         * @param  Container $container Pimple DI container.
         * @return TranslatorConfig
         */
        $container['translator/config'] = function (Container $container) {
            $appConfig   = isset($container['config']) ? $container['config'] : [];
            $transConfig = isset($appConfig['translator']) ? $appConfig['translator'] : null;
            if (isset($transConfig['paths'])) {
                $transConfig['paths'] = $container['config']->resolveValues($transConfig['paths']);
            }
            return new TranslatorConfig($transConfig);
        };

        /**
         * Dictionary of translations grouped by domain and locale, from translator config.
         *
         * @param  Container $container Pimple DI container.
         * @return array
         */
        $container['translator/translations'] = function (Container $container) {
            $transConfig = $container['translator/config'];
            return $transConfig['translations'];
        };

        /**
         * Instance of the Message Selector, that is used to resolve a translation.
         *
         * @return MessageSelector
         */
        $container['translator/message-selector'] = function () {
            return new MessageSelector();
        };

        /**
         * Instance of the Message Formatter, that is used to format a localized message.
         *
         * @param  Container $container Pimple DI container.
         * @return MessageFormatter
         */
        $container['translator/message-formatter'] = function (Container $container) {
            return new MessageFormatter($container['translator/message-selector']);
        };

        /**
         * Instance of the Translator, that is used for translation.
         *
         * @todo   Improve file loader with a map of file formats.
         * @param  Container $container Pimple DI container.
         * @return Translator
         */
        $container['translator'] = function (Container $container) {
            $transConfig = $container['translator/config'];
            $translator  = new Translator([
                'manager'           => $container['locales/manager'],
                'message_selector'  => $container['translator/message-selector'],
                'message_formatter' => $container['translator/message-formatter'],
                'cache_dir'         => $transConfig['cache_dir'],
                'debug'             => $transConfig['debug'],
            ]);

            $translator->setFallbackLocales($container['locales/fallback-languages']);

            $translator->addLoader('array', $container['translator/loader/array']);

            foreach ($transConfig['loaders'] as $loader) {
                $translator->addLoader($loader, $container['translator/loader/file/' . $loader]);

                $paths = array_reverse($transConfig['paths']);
                foreach ($paths as $path) {
                    $path = realpath($container['config']['base_path'] . DIRECTORY_SEPARATOR . $path);

                    if ($path === false) {
                        continue;
                    }

                    $files = glob($path . '/*.' . $loader);
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

        $this->registerTranslatorLoaders($container);
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    private function registerTranslatorLoaders(Container $container)
    {
        /**
         * @return ArrayLoader
         */
        $container['translator/loader/array'] = function () {
            return new ArrayLoader();
        };

        /**
         * @return CsvFileLoader
         */
        $container['translator/loader/file/csv'] = function () {
            return new CsvFileLoader();
        };

        /**
         * @return IcuDatFileLoader
         */
        $container['translator/loader/file/dat'] = function () {
            return new IcuDatFileLoader();
        };

        /**
         * @return IcuResFileLoader
         */
        $container['translator/loader/file/res'] = function () {
            return new IcuResFileLoader();
        };

        /**
         * @return IniFileLoader
         */
        $container['translator/loader/file/ini'] = function () {
            return new IniFileLoader();
        };

        /**
         * @return JsonFileLoader
         */
        $container['translator/loader/file/json'] = function () {
            return new JsonFileLoader();
        };

        /**
         * @return MoFileLoader
         */
        $container['translator/loader/file/mo'] = function () {
            return new MoFileLoader();
        };

        /**
         * @return PhpFileLoader
         */
        $container['translator/loader/file/php'] = function () {
            return new PhpFileLoader();
        };

        /**
         * @return PoFileLoader
         */
        $container['translator/loader/file/po'] = function () {
            return new PoFileLoader();
        };

        /**
         * @return QtFileLoader
         */
        $container['translator/loader/file/qt'] = function () {
            return new QtFileLoader();
        };

        /**
         * @return XliffFileLoader
         */
        $container['translator/loader/file/xliff'] = function () {
            return new XliffFileLoader();
        };

        /**
         * @return YamlFileLoader
         */
        $container['translator/loader/file/yaml'] = function () {
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
         * @param  Container $container
         * @return LanguageMiddleware
         */
        $container['middlewares/charcoal/translator/middleware/language'] = function (Container $container) {
            $middlewareConfig = $container['config']['middlewares']['charcoal/translator/middleware/language'];
            $middlewareConfig = array_replace(
                [
                    'default_language'  => $container['translator']->getLocale(),
                ],
                $middlewareConfig,
                [
                    'translator'        => $container['translator'],
                    'browser_language'  => $container['locales/browser-language'],
                ]
            );
            return new LanguageMiddleware($middlewareConfig);
        };
    }
}
