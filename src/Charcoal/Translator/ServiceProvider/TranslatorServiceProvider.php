<?php

namespace Charcoal\Translator\ServiceProvider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\CsvFileLoader;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

use Charcoal\Translator\LocalesConfig;
use Charcoal\Translator\LocalesManager;
use Charcoal\Translator\Translator;
use Charcoal\Translator\TranslatorConfig;

/**
 *
 */
class TranslatorServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    public function register(Container$container)
    {
        $this->registerLocales($container);
        $this->registerTranslator($container);
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    private function registerLocales(Container $container)
    {
        /**
         * @param Container $container Pimple DI container.
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
         * @param Container $container Pimple DI container.
         * @return string[]
         */
        $container['locales/languages'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return array_keys($localesConfig['languages']);
        };

        /**
         * Retrieve the list of locales (as configuration structure) available.
         *
         * @param Container $container Pimple DI container.
         * @return array
         */
        $container['locales/locales'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return $localesConfig['languages'];
        };

        /**
         * @param Container $container Pimple DI container.
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
         * @param Container $container Pimple DI container.
         * @return string|null
         */
        $container['locales/browser-language'] = function(Container $container) {
            if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                return null;
            }
            $availableLanguages = $container['locales/languages'];
            $acceptedLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($acceptedLanguages as $acceptedLang) {
                $lang = explode(';', $acceptedLang);
                if (in_array($lang[0], $availableLanguages)) {
                    return $lang[0];
                }
            }
            return null;
        };

        /**
         * @param Container $container Pimple DI container.
         * @return array
         */
        $container['locales/fallback-languages'] = function(Container $container) {
            $localesConfig = $container['locales/config'];
            return $localesConfig['fallback_languages'];
        };

        /**
         * @param Container $container Pimple DI container.
         * @return array
         */
        $container['locales/manager'] = function (Container $container) {
            return new LocalesManager([
                'locales'            => $container['locales/locales'],
                'default_language'     => $container['locales/default-language'],
                'fallback_languages'   => $container['locales/fallback-languages']
            ]);
        };
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    private function registerTranslator(Container $container)
    {

        /**
         * @param Container $container Pimple DI container.
         * @return TranslatorConfig
         */
        $container['translator/config'] = function (Container $container) {
            $config = isset($container['config']) ? $container['config'] : [];
            $translatorConfig = isset($config['translator']) ? $config['translator'] : null;
            return new TranslatorConfig($translatorConfig);
        };

        /**
         * @return array
         */
        $container['translator/domains'] = function () {
            return [];
        };

        /**
         * @return MessageSelector
         */
        $container['translator/message-selector'] = function () {
            return null;
        };

        /**
         * @param Container $container Pimple DI container.
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

            $translator->addLoader('array', new ArrayLoader());
            foreach ($translatorConfig['loaders'] as $loader) {
                $translator->addLoader($loader, $container['translator/loader/'.$loader]);
                foreach ($translatorConfig['paths'] as $path) {
                    $files = glob($path.'*.'.$loader);
                    foreach ($files as $f) {
                        $names = explode('.', basename($f));
                        if (count($names) < 2) {
                            continue;
                        }
                        $lang = $names[1];
                        $domain = $names[0];
                        $translator->addResource($loader, $f, $lang, $domain);
                    }
                }
            }

            foreach ($container['translator/domains'] as $locale => $translations) {
                $translator->addResource('array', $translations, $locale);
            }

            return $translator;
        };

        /**
         * @return CsvFileLoader
         */
        $container['translator/loader/csv'] = function() {
            return new CsvFileLoader();
        };

        /**
         * @return JsonFileLoader
         */
        $container['translator/loader/json'] = function() {
            return new JsonFileLoader();
        };

        /**
         * @return MoFileLoader
         */
        $container['translator/loader/mo'] = function() {
            return new MoFileLoader();
        };

        /**
         * @return PhpFileLoader
         */
        $container['translator/loader/php'] = function() {
            return new PhpFileLoader();
        };

        /**
         * @return PoFileLoader
         */
        $container['translator/loader/po'] = function() {
            return new PoFileLoader();
        };

        /**
         * @return XliffFileLoader
         */
        $container['translator/loader/xliff'] = function() {
            return new XliffFileLoader();
        };

        /**
         * @return YamlFileLoader
         */
        $container['translator/loader/yaml'] = function() {
            return new YamlFileLoader();
        };
    }
}
