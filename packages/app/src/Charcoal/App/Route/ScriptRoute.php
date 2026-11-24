<?php

namespace Charcoal\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal-app'
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\ScriptRouteConfig;
use Charcoal\App\Script\ScriptInterface;

/**
 * Script Route Handler.
 */
class ScriptRoute implements
    ConfigurableInterface,
    RouteInterface
{
    use ConfigurableTrait;

    /**
     * Create new script route (CLI)
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
    }

    /**
     * @param mixed|null $data Optional config data.
     * @return ScriptRouteConfig
     * @see ConfigurableTrait::createConfig()
     */
    public function createConfig($data = null)
    {
        return new ScriptRouteConfig($data);
    }

    /**
     * @param Container         $container A dependencies container.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $script = $this->createScript($container, $request);

        return $script($request, $response);
    }

    /**
     * @param Container         $container A container instance.
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @return ScriptInterface
     */
    protected function createScript(Container $container, RequestInterface $request)
    {
        $config = $this->config();

        return $container['script/factory']->create($config['controller']);
    }
}
