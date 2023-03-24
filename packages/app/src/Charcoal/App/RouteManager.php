<?php

namespace Charcoal\App;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
use Slim\App;

/**
 * The route manager takes care of dispatching each route from an app or a module config
 */
final class RouteManager
{
    /**
     * Set up the routes.
     *
     * There are 3 types of routes:
     *
     * - Templates
     * - Actions
     * - Scripts
     *
     * @return void
     */
    public function setupRoutes(App $app, array $routes)
    {
        $routeMapper = new RouteMapper();

        if (PHP_SAPI == 'cli') {
            $scripts = ($routes['scripts'] ?? []);
            $scriptMapper($app, $this->parseScripts($scripts));
        } else {
            $templates = ($routes['templates'] ?? []);
            $actions = ($routes['actions'] ?? []);

            $routeMapper($app, $this->parseTemplates($templates));
            $routeMapper($app, $this->parseActions($actions));
        }

        // Map all remaining routes
        unset($routes['templates'], $routes['actions'], $routes['scripts']);

        $routeMapper($app, $routes);
    }

    public function setupRedirections(App $app, array $redirections)
    {
        $routeMapper = new RouteMapper();
        $routeMapper($app, $this->parseRedirections($redirections));
    }

    private function parseRedirections(array $redirections): array
    {
        $ret = [];
        foreach ($redirections as $key => $redirectionOptions) {
            if (is_string($redirectionOptions)) {
                $redirectionOptions = ['target' => $redirectionOptions];
            }
            $redirectionOptions['type'] = 'redirection';
            $ret[$key] = $redirectionOptions;
        }
        return $ret;
    }

    private function parseTemplates(array $templates): array
    {
        $ret = [];
        foreach ($templates as $key => $templateOptions) {
            $templateOptions['type'] ??= 'template';
            $ret[$key] = $templateOptions;
        }
        return $ret;
    }

    private function parseActions(array $actions): array
    {
        $ret = [];
        foreach ($actions as $key => $actionOptions) {
            $actionOptions['type'] ??= 'action';
            $ret[$key] = $actionOptions;
        }
        return $ret;
    }

    private function parseScripts(array $scripts): array
    {
        $ret = [];
        foreach ($scripts as $key => $scriptOptions) {
            $scriptOptions['type'] = 'script';
            $ret[$key] = $scriptOptions;
        }
        return $ret;
    }
}
