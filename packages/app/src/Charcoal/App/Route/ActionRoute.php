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
use Charcoal\App\Route\ActionRouteConfig;
use Psr\Container\ContainerInterface;

/**
 * Action Route Handler.
 */
class ActionRoute implements
    RouteInterface,
    ConfigurableInterface
{
    use ConfigurableTrait;

    private ContainerInterface $container;

    /**
     * Create new action route
     *
     * ### Dependencies
     *
     * **Required**
     *
     * - `config` — ActionRouteConfig
     *
     * **Optional**
     *
     * - `logger` — PSR-3 Logger
     *
     * @param array $data Dependencies.
     */
    public function __construct(array $data)
    {
        $this->setConfig($data['config']);
        $this->container = $data['container'];
    }

    /**
     * ConfigurableTrait > createConfig()
     *
     * @param mixed|null $data Optional config data.
     * @return ActionRouteConfig
     */
    public function createConfig($data = null)
    {
        return new ActionRouteConfig($data);
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

        $actionController = $config['controller'];

        $action = $container->get('action/factory')->create($actionController);
        $action->init($request);

        // Set custom data from config.
        $action->setData($config['action_data']);

        // Set headers if necessary.
        if (!empty($config['headers'])) {
            foreach ($config['headers'] as $name => $val) {
                $response = $response->withHeader($name, $val);
            }
        }

        // Run (invoke) action.
        return $action($request, $response);
    }
}
