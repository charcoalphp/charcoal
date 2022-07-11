<?php

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
use Charcoal\View\Twig\DebugHelpers;
use Charcoal\View\Twig\TwigLoader;

/**
 *
 */
class DebugHelpersTest extends AbstractTestCase
{
    /**
     * @var AppConfig
     */
    private $config;

    /**
     * @var TwigEngine
     */
    private $twig;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->config = [];
        $this->twig   = $this->createTwigEngine($this->config);
    }

    /**
     * @param  AppConfig|null $config The app config for the debug helpers.
     * @return TwigEnvironment
     */
    public function createTwigEngine($config = null)
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

        $helper   = new DebugHelpers([
            'config' => $config,
        ]);
        $twig->setExtensions($helper->toArray());

        return $twig;
    }
}
