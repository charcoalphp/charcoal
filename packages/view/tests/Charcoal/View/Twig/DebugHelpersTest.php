<?php

namespace Charcoal\Tests\View\Twig;

// From Twig
use Twig\Environment as TwigEnvironment;
// From 'charcoal-view'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\Twig\DebugHelpers;
use Charcoal\View\Twig\TwigLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use Charcoal\View\Twig\TwigEngine;

#[CoversClass(DebugHelpers::class)]
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
        $this->markTestIncomplete();

        $this->config = [];
        $this->twig   = $this->createTwigEngine($this->config);
    }

    public function testDebugHelpers()
    {
        $this->assertInstanceOf(TwigEngine::class, $this->twig);
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
