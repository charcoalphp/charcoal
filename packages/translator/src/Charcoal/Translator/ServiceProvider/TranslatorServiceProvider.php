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
     */
    public function register(Container $container): void
    {
        $this->registerLocales($container);
        $this->registerTranslator($container);
        $this->registerMiddleware($container);
    }

    /**
     * @param  Container $container Pimple DI container.
     */
    private function registerLocales(Container $container): void
    {
        /**
         * Instance of the Locales Configset.
         *
         * @param  Container $container Pimple DI container.
         * @return LocalesConfig
         */
        $container['locales/config'] = function (Container $container): \Charcoal\Translator\LocalesConfig {
            $appConfig     = ($container['config'] ?? []);
            $localesConfig = $appConfig['locales'] ?? null;
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
            if (isset($localesConfig['auto_detect']) && $localesConfig['auto_detect'] && $container['locales/browser-language'] !== null) {
                return $container['locales/browser-language'];
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
        $container['locales/browser-language'] = function (Container $container): ?string {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return null;
            }

            /**
             * Using data from configset instead of LocalesManager
             * since the latter might need the browser language
             * as the default language.
             */
            $localesConfig    = $container['locales/config'];
            $supportedLocales = array_filter($localesConfig['languages'], fn(array $locale): bool => !(isset($locale['active']) && !$locale['active']));

            $acceptableLanguages = explode(',', (string)$_SERVER['HTTP_ACCEPT_LANGUAGE']);
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
        $container['locales/manager'] = function (Container $container): \Charcoal\Translator\LocalesManager {
            $localesConfig = $container['locales/config'];
            return new LocalesManager([
                'locales'          => $localesConfig['languages'],
                'default_language' => $container['locales/default-language'],
            ]);
        };
    }

    /**
     * @param  Container $container Pimple DI container.
     */
    private function registerTranslator(Container $container): void
    {
        /**
         * Instance of the Translator Configset.
         *
         * @param  Container $container Pimple DI container.
         * @return TranslatorConfig
         */
        $container['translator/config'] = function (Container $container): \Charcoal\Translator\TranslatorConfig {
            $appConfig   = ($container['config'] ?? []);
            $transConfig = $appConfig['translator'] ?? null;

            if (isset($transConfig['paths'])) {
                $transConfig['paths'] = $appConfig->resolveValues($transConfig['paths']);
            }

            $transConfig = new TranslatorConfig($transConfig);

            if (isset($container['module/classes'])) {
                $extraPaths = [];
                $basePath   = $appConfig['base_path'];
                $modules    = $container['module/classes'];
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::APP_CONFIG', $module))) {
                        $configPath = ltrim((string)$module::APP_CONFIG, '/');
                        $configPath = $basePath . DIRECTORY_SEPARATOR . $configPath;

                        $configData = $appConfig->loadFile($configPath);
                        if (isset($configData['translator']['paths'])) {
                            $extraPaths = array_merge(
                                $extraPaths,
                                $appConfig->resolveValues($configData['translator']['paths'])
                            );
                        }
                    };
                }

                if ($extraPaths !== []) {
                    $transConfig->addPaths($extraPaths);
                }
            }

            return $transConfig;
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
         * Instance of the Message Formatter, that is used to format a localized message.
         *
         * @return MessageFormatter
         */
        $container['translator/message-formatter'] = (fn(): \Symfony\Component\Translation\Formatter\MessageFormatter => new MessageFormatter());

        /**
         * Instance of the Translator, that is used for translation.
         *
         * @todo   Improve file loader with a map of file formats.
         * @param  Container $container Pimple DI container.
         * @return Translator
         */
        $container['translator'] = function (Container $container): \Charcoal\Translator\Translator {
            $transConfig = $container['translator/config'];
            $translator  = new Translator([
                'manager'           => $container['locales/manager'],
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
     */
    private function registerTranslatorLoaders(Container $container): void
    {
        /**
         * @return ArrayLoader
         */
        $container['translator/loader/array'] = (fn(): \Symfony\Component\Translation\Loader\ArrayLoader => new ArrayLoader());

        /**
         * @return CsvFileLoader
         */
        $container['translator/loader/file/csv'] = (fn(): \Symfony\Component\Translation\Loader\CsvFileLoader => new CsvFileLoader());

        /**
         * @return IcuDatFileLoader
         */
        $container['translator/loader/file/dat'] = (fn(): \Symfony\Component\Translation\Loader\IcuDatFileLoader => new IcuDatFileLoader());

        /**
         * @return IcuResFileLoader
         */
        $container['translator/loader/file/res'] = (fn(): \Symfony\Component\Translation\Loader\IcuResFileLoader => new IcuResFileLoader());

        /**
         * @return IniFileLoader
         */
        $container['translator/loader/file/ini'] = (fn(): \Symfony\Component\Translation\Loader\IniFileLoader => new IniFileLoader());

        /**
         * @return JsonFileLoader
         */
        $container['translator/loader/file/json'] = (fn(): \Symfony\Component\Translation\Loader\JsonFileLoader => new JsonFileLoader());

        /**
         * @return MoFileLoader
         */
        $container['translator/loader/file/mo'] = (fn(): \Symfony\Component\Translation\Loader\MoFileLoader => new MoFileLoader());

        /**
         * @return PhpFileLoader
         */
        $container['translator/loader/file/php'] = (fn(): \Symfony\Component\Translation\Loader\PhpFileLoader => new PhpFileLoader());

        /**
         * @return PoFileLoader
         */
        $container['translator/loader/file/po'] = (fn(): \Symfony\Component\Translation\Loader\PoFileLoader => new PoFileLoader());

        /**
         * @return QtFileLoader
         */
        $container['translator/loader/file/qt'] = (fn(): \Symfony\Component\Translation\Loader\QtFileLoader => new QtFileLoader());

        /**
         * @return XliffFileLoader
         */
        $container['translator/loader/file/xliff'] = (fn(): \Symfony\Component\Translation\Loader\XliffFileLoader => new XliffFileLoader());

        /**
         * @return YamlFileLoader
         */
        $container['translator/loader/file/yaml'] = (fn(): \Symfony\Component\Translation\Loader\YamlFileLoader => new YamlFileLoader());
    }

    /**
     * @param  Container $container Pimple DI container.
     */
    private function registerMiddleware(Container $container): void
    {
        /**
         * @param  Container $container
         * @return LanguageMiddleware
         */
        $container['middlewares/charcoal/translator/middleware/language'] = function (Container $container): \Charcoal\Translator\Middleware\LanguageMiddleware {
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
