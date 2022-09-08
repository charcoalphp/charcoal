<?php

namespace Charcoal\Tests\Translation\Script;

use ReflectionClass;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Script\TranslationParserScript;
use Charcoal\Tests\Translator\ContainerProvider;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class TranslationParserScriptTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var TranslationParserScript
     */
    private $obj;

    /**
     * CLImate Output
     *
     * @var \League\CLImate\Util\Output|\Mockery\MockInterface
     */
    public $output;

    /**
     * Service Container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->container = $this->getContainer();

        $this->output = $this->container['climate']->output;

        $this->obj = new TranslationParserScript([
            'logger'        => $this->container['logger'],
            'climate'       => $this->container['climate'],
            'model_factory' => $this->container['model/factory'],
            'container'     => $this->container
        ]);
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        $container = new Container();
        $provider  = new ContainerProvider();

        $provider->registerBaseServices($container);
        $provider->registerModelServices($container);
        $provider->registerAdminServices($container);

        return $container;
    }

    /**
     * @return void
     */
    public function testDefaultArguments()
    {
        $args = $this->obj->defaultArguments();
        $this->assertArrayHasKey('domain', $args);
    }

    /**
     * @return void
     */
    /*
    public function testRun()
    {
        $container = $this->getContainer();
        $request   = $this->createMock(RequestInterface::class);
        $response  = $this->createMock(ResponseInterface::class);

        $filePath = rtrim($container['config']['base_path'], '/\\') . '/translations/';
        $fileName = 'messages.{locale}.csv';
        $fileType = 'mustache';
        $maxDepth = 4;

        $this->shouldWrite("Initializing translations parser script...");
        $this->shouldWrite("\e[m\e[32mCSV file output: \e[97m" . $filePath . "\e[0m\e[0m\e[0m");
        $this->shouldWrite("\e[m\e[32mCSV file names: \e[97m" . $fileName . "\e[0m\e[0m\e[0m");
        $this->shouldWrite("\e[m\e[32mLooping through \e[97m" . $maxDepth . "\e[0m level of folders\e[0m\e[0m");
        $this->shouldWrite("\e[m\e[32mFile type parsed: \e[97m" . $fileType . "\e[0m\e[0m\e[0m");
        $this->shouldHavePersisted(5);

        $resp = $this->obj->run($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $resp);
    }
    */



    // CLImate Helpers
    // =============================================================================================

    /**
     * @param  string  $content The expected content.
     * @param  integer $times   The number of times this expectation should occur.
     * @return mixed
     */
    protected function shouldWrite($content, $times = 1)
    {
        return $this->output->shouldReceive('write')->times($times)->with($content);
    }

    /**
     * @param  integer $times The number of times this expectation should occur.
     * @return void
     */
    protected function shouldHavePersisted($times = 1)
    {
        $this->shouldStartPersisting($times);
        $this->shouldStopPersisting($times);
    }

    /**
     * @param  integer $times The number of times this expectation should occur.
     * @return void
     */
    protected function shouldStartPersisting($times = 1)
    {
        $this->output->shouldReceive('persist')->withNoArgs()->times($times)->andReturn($this->output);
    }

    /**
     * @param  integer $times The number of times this expectation should occur.
     * @return void
     */
    protected function shouldStopPersisting($times = 1)
    {
        $this->output->shouldReceive('persist')->with(false)->times($times)->andReturn($this->output);
    }
}
