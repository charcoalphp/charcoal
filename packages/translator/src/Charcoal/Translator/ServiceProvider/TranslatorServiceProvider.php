<?php

namespace Charcoal\Translator\ServiceProvider;

use DI\Container;
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
class TranslatorServiceProvider
{
    /**
     * @param  Container $container DI Container.
     * @return void
     */
    public function register(Container $container)
    {
        $this->registerLocales($container);
        $this->registerTranslator($container);
        $this->registerMiddleware($container);
    }

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    private function registerLocales(Container $container)
    {
        /**
         * Instance of the Locales Configset.
         *
         * @param  Container $container DI Container.
         * @return LocalesConfig
         */
        $container->set('locales/config', function (Container $container) {
            $appConfig     = $container->has('config') ? $container->get('config') : [];
            $localesConfig = isset($appConfig['locales']) ? $appConfig['locales'] : null;
            return new LocalesConfig($localesConfig);
        });

        /**
         * Default language of the application, optionally the navigator's preferred language.
         *
         * @param  Container $container DI Container.
         * @return string|null
         */
        $container->set('locales/default-language', function (Container $container) {
            $localesConfig = $container->get('locales/config');
            if (isset($localesConfig['auto_detect']) && $localesConfig['auto_detect']) {
                if ($container->get('locales/browser-language') !== null) {
                    return $container->get('locales/browser-language');
                }
            }
            return $localesConfig['default_language'];
        });

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
         * @param  Container $container DI Container.
         * @return string|null
         */
        $container->set('locales/browser-language', function (Container $container) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return null;
            }

            /**
             * Using data from configset instead of LocalesManager
             * since the latter might need the browser language
             * as the default language.
             */
            $localesConfig    = $container->get('locales/config');
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
        });

        /**
         * List of fallback language codes for the translator.
         *
         * @todo   Use filtered "fallback_languages" from LocalesManager
         * @param  Container $container DI Container.
         * @return string[]
         */
        $container->set('locales/fallback-languages', function (Container $container) {
            $localesConfig = $container->get('locales/config');
            return $localesConfig['fallback_languages'];
        });

        /**
         * List of language codes (locale ident) from the available locales.
         *
         * @param  Container $container DI Container.
         * @return string[]
         */
        $container->set('locales/available-languages', function (Container $container) {
            $manager = $container->get('locales/manager');
            return $manager->availableLocales();
        });

        /**
         * List of available locales (as configuration structures) of the application.
         *
         * @param  Container $container DI Container.
         * @return array
         */
        $container->set('locales/languages', function (Container $container) {
            $manager = $container->get('locales/manager');
            return $manager->locales();
        });

        /**
         * Instance of the Locales Manager.
         *
         * @todo   Filter "fallback_languages"
         * @param  Container $container DI Container.
         * @return LocalesManager
         */
        $container->set('locales/manager', function (Container $container) {
            $localesConfig = $container->get('locales/config');
            return new LocalesManager([
                'locales'          => $localesConfig['languages'],
                'default_language' => $container->get('locales/default-language'),
            ]);
        });
    }

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    private function registerTranslator(Container $container)
    {
        /**
         * Instance of the Translator Configset.
         *
         * @param  Container $container DI Container.
         * @return TranslatorConfig
         */
        $container->set('translator/config', function (Container $container) {
            $appConfig   = $container->has('config') ? $container->get('config') : [];
            $transConfig = isset($appConfig['translator']) ? $appConfig['translator'] : null;

            if (isset($transConfig['paths'])) {
                $transConfig['paths'] = $appConfig->resolveValues($transConfig['paths']);
            }

            $transConfig = new TranslatorConfig($transConfig);

            if ($container->has('module/classes')) {
                $extraPaths = [];
                $basePath   = $appConfig['base_path'];
                $modules    = $container->get('module/classes');
                foreach ($modules as $module) {
                    if (defined(sprintf('%s::APP_CONFIG', $module))) {
                        $configPath = ltrim($module::APP_CONFIG, '/');
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

                if ($extraPaths) {
                    $transConfig->addPaths($extraPaths);
                }
            }

            return $transConfig;
        });

        /**
         * Dictionary of translations grouped by domain and locale, from translator config.
         *
         * @param  Container $container DI Container.
         * @return array
         */
        $container->set('translator/translations', function (Container $container) {
            $transConfig = $container->get('translator/config');
            return $transConfig['translations'];
        });

        /**
         * Instance of the Message Formatter, that is used to format a localized message.
         *
         * @param  Container $container DI Container.
         * @return MessageFormatter
         */
        $container->set('translator/message-formatter', function (Container $container) {
            return new MessageFormatter();
        });

        /**
         * Instance of the Translator, that is used for translation.
         *
         * @todo   Improve file loader with a map of file formats.
         * @param  Container $container DI Container.
         * @return Translator
         */
        $container->set('translator', function (Container $container) {
            $transConfig = $container->get('translator/config');
            $translator  = new Translator([
                'manager'           => $container->get('locales/manager'),
                'message_formatter' => $container->get('translator/message-formatter'),
                'cache_dir'         => $transConfig['cache_dir'],
                'debug'             => $transConfig['debug'],
            ]);

            $translator->setFallbackLocales($container->get('locales/fallback-languages'));

            $translator->addLoader('array', $container->get('translator/loader/array'));

            foreach ($transConfig['loaders'] as $loader) {
                $translator->addLoader($loader, $container->get('translator/loader/file/' . $loader));

                $paths = array_reverse($transConfig['paths']);
                foreach ($paths as $path) {
                    $path = realpath($container->get('config')['base_path'] . DIRECTORY_SEPARATOR . $path);

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

                        // Validate CSV files before loading
                        if ($loader === 'csv' && !$this->isValidTranslationCsv($f)) {
                            continue;
                        }

                        $translator->addResource($loader, $f, $lang, $domain);
                    }
                }
            }

            foreach ($container->get('translator/translations') as $domain => $data) {
                foreach ($data as $locale => $messages) {
                    $translator->addResource('array', $messages, $locale, $domain);
                }
            }

            return $translator;
        });

        $this->registerTranslatorLoaders($container);
    }

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    private function registerTranslatorLoaders(Container $container)
    {
        /**
         * @return ArrayLoader
         */
        $container->set('translator/loader/array', function () {
            return new ArrayLoader();
        });

        /**
         * @return CsvFileLoader
         */
        $container->set('translator/loader/file/csv', function () {
            return new CsvFileLoader();
        });

        /**
         * @return IcuDatFileLoader
         */
        $container->set('translator/loader/file/dat', function () {
            return new IcuDatFileLoader();
        });

        /**
         * @return IcuResFileLoader
         */
        $container->set('translator/loader/file/res', function () {
            return new IcuResFileLoader();
        });

        /**
         * @return IniFileLoader
         */
        $container->set('translator/loader/file/ini', function () {
            return new IniFileLoader();
        });

        /**
         * @return JsonFileLoader
         */
        $container->set('translator/loader/file/json', function () {
            return new JsonFileLoader();
        });

        /**
         * @return MoFileLoader
         */
        $container->set('translator/loader/file/mo', function () {
            return new MoFileLoader();
        });

        /**
         * @return PhpFileLoader
         */
        $container->set('translator/loader/file/php', function () {
            return new PhpFileLoader();
        });

        /**
         * @return PoFileLoader
         */
        $container->set('translator/loader/file/po', function () {
            return new PoFileLoader();
        });

        /**
         * @return QtFileLoader
         */
        $container->set('translator/loader/file/qt', function () {
            return new QtFileLoader();
        });

        /**
         * @return XliffFileLoader
         */
        $container->set('translator/loader/file/xliff', function () {
            return new XliffFileLoader();
        });

        /**
         * @return YamlFileLoader
         */
        $container->set('translator/loader/file/yaml', function () {
            return new YamlFileLoader();
        });
    }

    /**
     * @param  Container $container DI Container.
     * @return void
     */
    private function registerMiddleware(Container $container)
    {
        /**
         * @param  Container $container
         * @return LanguageMiddleware
         */
        $container->set('middlewares/charcoal/translator/middleware/language', function (Container $container) {
            $middlewareConfig = $container->get('config')['middlewares']['charcoal/translator/middleware/language'];
            $middlewareConfig = array_replace(
                [
                    'default_language'  => $container->get('translator')->getLocale(),
                ],
                $middlewareConfig,
                [
                    'translator'        => $container->get('translator'),
                    'browser_language'  => $container->get('locales/browser-language'),
                ]
            );
            return new LanguageMiddleware($middlewareConfig);
        });
    }

    private function isValidTranslationCsv($file)
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            return false;
        }
        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            if (empty($row) || !isset($row[0], $row[1])) {
                fclose($handle);
                return false;
            }
        }
        fclose($handle);
        return true;
    }
}
