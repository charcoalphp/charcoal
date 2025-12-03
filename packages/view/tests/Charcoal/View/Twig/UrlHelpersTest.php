<?php

namespace Charcoal\Tests\View\Twig;

// From Twig
use Twig\Environment as TwigEnvironment;
// From 'charcoal-view'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\View\Twig\TwigEngine;
use Charcoal\View\Twig\UrlHelpers;
use Charcoal\View\Twig\TwigLoader;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlHelpers::class)]
class UrlHelpersTest extends AbstractTestCase
{
    /**
     * @var Uri
     */
    private $baseUrl;

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
        $this->baseUrl = [];
        $this->twig   = $this->createTwigEngine($this->baseUrl);
    }

    public function testUrlHelpers()
    {
        $this->assertInstanceOf(TwigEngine::class, $this->twig);
    }

    /**
     * @param  Uri|null $baseUrl The base Url.
     * @return TwigEnvironment
     */
    public function createTwigEngine($baseUrl = null)
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
