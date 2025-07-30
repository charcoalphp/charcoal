<?php

namespace Charcoal\App\Route;

use InvalidArgumentException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Container;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal-app'
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\ScriptRouteConfig;
use Psr\Container\ContainerInterface;

/**
 * Script Route Handler.
 */
class ScriptRoute implements
    ConfigurableInterface,
    RouteInterface
{
    use ConfigurableTrait;

    private ContainerInterface $container;

    /**
     * Create new script route (CLI)
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
        $this->container = $data['container'];
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
     * @param RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $config = $this->config();
        $container = $this->container;

        $script = $container->get('script/factory')->create($config['controller']);

        return $script($request, $response);
    }
}
