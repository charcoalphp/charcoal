<?php

namespace Charcoal\App\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Container;
// From Slim
use Nyholm\Psr7\Uri;
// From 'charcoal-config'
use Charcoal\Config\ConfigurableInterface;
use Charcoal\Config\ConfigurableTrait;
// From 'charcoal-app'
use Charcoal\App\Route\RouteInterface;
use Charcoal\App\Route\TemplateRouteConfig;
use Psr\Container\ContainerInterface;

/**
 * Template Route Handler.
 *
 * A route handler is a simple `invokale` object with the signature:
 * `__invoke(Container $container, RequestInterface $request, ResponseInterface $response)`
 * It is only called (invoked) when a route is matched.
 *
 * This is the default "Slim Route Handler" for _template_ style routes.
 * It uses a `TemplateRouteConfig` to properly either:
 *
 * - redirect the request, if explicitely set
 * - load and render a "Template" object
 *
 * Templates can be any objects that can be loaded with a "TemplateFactory".
 * The Template Factory used is an external dependency (`template/factory`) expected to be set on the container.
 *
 * Template-level cache is possible by setting the "cache" config option to true.
 * Cached template can not have any options; they will always return the exact same content for all "template".
 *
 */
class TemplateRoute implements
    ConfigurableInterface,
    RouteInterface
{
    use ConfigurableTrait;

    protected ContainerInterface $container;

    /**
     * Create new template route
     *
     * **Required dependencies**
     *
     * - `config` — TemplateRouteConfig
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
     * @param  mixed|null $data Optional config data.
     * @return TemplateRouteConfig
     */
    public function createConfig($data = null)
    {
        return new TemplateRouteConfig($data);
    }

    /**
     * @param  RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $config = $this->config();
        $container = $this->container;

        // Handle explicit redirects
        if (!empty($config['redirect'])) {
            $redirect = $container->get('translator')->translation($config['redirect']);
            $uri = $this->parseRedirect((string)$redirect, $request);

            if ($uri) {
                return $response
                    ->withHeader('Location', (string)$uri)
                    ->withStatus($config['redirect_mode']);
            }
        }

        $templateContent = $this->templateContent($container, $request);

        $response->getBody()->write($templateContent);

        if (!empty($config['headers'])) {
            foreach ($config['headers'] as $name => $val) {
                $response = $response->withHeader($name, $val);
            }
        }


        return $response;
    }

    /**
     * @param  Container        $container A DI (DI) container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function templateContent(
        ContainerInterface $container,
        RequestInterface $request
    ) {
        if ($this->cacheEnabled()) {
            $cachePool = $container->get('cache');
            $cacheKey  = 'template/' . str_replace('/', '.', $this->cacheIdent());
            $cacheItem = $cachePool->getItem($cacheKey);

            $template = $cacheItem->get();
            if ($cacheItem->isMiss()) {
                $template = $this->renderTemplate($container, $request);

                $cacheItem->set($template, $this->cacheTtl());
                $cachePool->save($cacheItem);
            }
        } else {
            $template = $this->renderTemplate($container, $request);
        }

        return $template;
    }

    /**
     * @param  \DI\Container $container A DI container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function renderTemplate(ContainerInterface $container, RequestInterface $request)
    {
        $config   = $this->config();
        $template = $this->createTemplate($container, $request);

        return $container->get('view')->render($config->get('template'), $template);
    }

    /**
     * @param  \DI\Container $container A DI container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function createTemplate(ContainerInterface $container, RequestInterface $request)
    {
        $config = $this->config();

        $templateFactory = $container->get('template/factory');
        if ($config['default_controller'] !== null) {
            $templateFactory->setDefaultClass($config['default_controller']);
        }

        $template = $templateFactory->create($config['controller']);
        $template->init($request);

        // Set custom data from config.
        $template->setData($config['template_data']);

        return $template;
    }

    /**
     * @param  string           $redirection The route's destination.
     * @param  RequestInterface $request     A PSR-7 compatible Request instance.
     * @return Uri|null
     */
    protected function parseRedirect($redirection, RequestInterface $request)
    {
        $uri   = $request->getUri()->withUserInfo('');
        $parts = parse_url($redirection);

        if (!empty($parts)) {
            if (isset($parts['host'])) {
                $uri = new Uri($redirection);
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

    /**
     * Determine if the cache is enabled.
     *
     * @return boolean
     */
    protected function cacheEnabled()
    {
        return $this->config('cache');
    }

    /**
     * Retrieve the time-to-live value for the cache.
     *
     * @return integer
     */
    protected function cacheTtl()
    {
        return $this->config('cache_ttl');
    }

    /**
     * Retrieve the cache key.
     *
     * @return string
     */
    protected function cacheIdent()
    {
        return $this->config('template');
    }
}
