<?php

declare(strict_types=1);

namespace Charcoal\Tests\View\Twig;

use LogicException;

// From Twig
use Twig\Environment as TwigEnvironment;

// From 'symfony/translation'
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;

// From 'charcoal-translator'
use Charcoal\Translator\Translator;
use Charcoal\Translator\LocalesManager;

// From 'charcoal-view'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\Twig\UrlHelpers;
use Charcoal\View\Twig\TwigLoader;

/**
 *
 */
class UrlHelpersTest extends AbstractTestCase
{
    public function setUp(): void
    {
    }

    /**
     * @param  Uri|null $baseUrl The base Url.
     */
    public function createTwigEngine($baseUrl = null): \Twig\Environment
    {
        $loader = new TwigLoader([
            'base_path' => __DIR__,
            'paths'     => [ 'templates' ],
        ]);
        $twig = new TwigEnvironment($loader, [
            'cache'             => false,
            'charset'           => 'utf-8',
            'auto_reload'       => false,
            'strict_variables'  => true,
            'debug'             => true,
        ]);

        $helper   = new UrlHelpers([
            'baseUrl' => $baseUrl,
        ]);
        $twig->setExtensions($helper->toArray());

        return $twig;
    }
}
