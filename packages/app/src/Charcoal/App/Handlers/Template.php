<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\Template\TemplateInterface;
use Charcoal\Slim\Utils\ClassResolver;
use Pimple\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\Uri;
use Slim\Psr7\Factory\UriFactory;

final class Template
{
    public const DEFAULT_METHODS = ['POST'];

    private ActionConfig $config;
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->config = new ActionConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        // Handle explicit redirects
        if (!empty($this->config['redirect'])) {
            $redirect = $this->container['translator']->translation($this->config['redirect']);
            $uri = $this->parseRedirect((string)$redirect, $request);

            if ($uri) {
                return $response
                    ->withHeader('Location', (string)$uri)
                    ->withStatus($config['redirect_mode']);
            }
        }

        $templateContent = $this->templateContent($request);

        $response->getBody()->write($templateContent);

        foreach ($this->config['headers'] as $name => $val) {
            $response = $response->withHeader($name, $val);
        }

        return $response;
    }

    private function templateContent(Request $request): string
    {
        if ($this->cacheEnabled()) {
            $cachePool = $this->container->get('cache');
            $cacheKey  = 'template/' . str_replace('/', '.', $this->cacheIdent());
            $cacheItem = $cachePool->getItem($cacheKey);

            $template = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $template = $this->renderTemplate($request);

                $cacheItem->set($template, $this->cacheTtl());
                $cachePool->save($cacheItem);
            }
        } else {
            $template = $this->renderTemplate($request);
        }

        return $template;
    }

    private function renderTemplate(Request $request): string
    {
        $template = $this->createTemplate($request);

        return $this->container->get('view')->render($this->config->get('template'), $template);
    }

    private function createTemplate(Request $request): TemplateInterface
    {
        $templateFactory = $this->container->get('template/factory');
        if ($this->config['default_controller'] !== null) {
            $templateFactory->setDefaultClass($this->config['default_controller']);
        }

        $template = $templateFactory->create($this->config->get('controller'));
        $template->init($request);

        if ($this->config->get('template_data')) {
            $template->setData($this->config->get('template_data'));
        }
        return $template;
    }

    /**
     * @param  string           $redirection The route's destination.
     * @param  RequestInterface $request     A PSR-7 compatible Request instance.
     * @return Uri|null
     */
    private function parseRedirect($redirection, RequestInterface $request)
    {
        $uri   = $request->getUri()->withUserInfo('');
        $parts = parse_url($redirection);

        if (!empty($parts)) {
            if (isset($parts['host'])) {
                $uri = (new UriFactory())->createUri($redirection);
            } else {
                if (isset($parts['path'])) {
                    $uri = $uri->withPath($parts['path']);
                }

                if (isset($parts['query'])) {
                    $uri = $uri->withQuery($parts['query']);
                }

                if (isset($parts['fragment'])) {
                    $uri = $uri->withFragment($parts['fragment']);
                }
            }

            if ((string)$uri !== (string)$request->getUri()) {
                return $uri;
            }
        }

        return null;
    }

    private function cacheEnabled(): bool
    {
        return !!$this->config->get('cache');
    }

    private function cacheTtl(): int
    {
        return (int)$this->config->get('cache_ttl');
    }

    private function cacheIdent(): string
    {
        return (string)$this->config->get('template');
    }
}
