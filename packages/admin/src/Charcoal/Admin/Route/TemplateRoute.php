<?php

namespace Charcoal\Admin\Route;

// From PSR-7
use Psr\Http\Message\RequestInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-app'
use Charcoal\App\Route\TemplateRoute as AppTemplateRoute;

class TemplateRoute extends AppTemplateRoute
{
    /**
     * @param  Container        $container A DI (Pimple) container.
     * @param  RequestInterface $request   The request to intialize the template with.
     * @return string
     */
    protected function renderTemplate(Container $container, RequestInterface $request)
    {
        $config   = $this->config();
        $template = $this->createTemplate($container, $request);

        return $container['admin/view']->render($config['template'], $template);
    }
}
