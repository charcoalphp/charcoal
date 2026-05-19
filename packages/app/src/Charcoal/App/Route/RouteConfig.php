<?php

namespace Charcoal\App\Route;

use InvalidArgumentException;
// From 'charcoal-config'
use Charcoal\Config\AbstractConfig;

/**
 * Base "Route" configuration.
 */
class RouteConfig extends AbstractConfig
{
    /**
     * Route identifier/name
     */
    private ?string $ident = null;

    /**
     * Route pattern
     */
    private ?string $route = null;

    /**
     * HTTP methods supported by this route
     *
     * @var string[]
     */
    private array $methods = [ 'GET' ];

    /**
     * Response controller classname
     *
     * Should be the class-ident of an action, a script or a template controller.
     */
    private ?string $controller = null;

    /**
     * Parent route groups
     *
     * @var string[]
     */
    private array $groups = [];

    /**
     * Optional headers to set on response.
     */
    private array $headers = [];

    /**
     * Retrieve the default route types.
     */
    public static function defaultRouteTypes(): array
    {
        return [
            'templates',
            'actions',
            'scripts'
        ];
    }

    /**
     * Set route identifier
     *
     * @param string $ident Route identifier.
     * @throws InvalidArgumentException If the identifier is not a string.
     */
    public function setIdent($ident): static
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException(
                'Route identifier must be a string.'
            );
        }

        $this->ident = $ident;

        return $this;
    }

    /**
     * Get route identifier
     *
     * @return string
     */
    public function ident(): ?string
    {
        return $this->ident;
    }

    /**
     * Set route pattern.
     *
     * @param string $pattern Route pattern.
     * @throws InvalidArgumentException If the pattern argument is not a string.
     */
    public function setRoute($pattern): static
    {
        if (!is_string($pattern)) {
            throw new InvalidArgumentException(
                'Route pattern must be a string.'
            );
        }

        $this->route = $pattern;

        return $this;
    }

    /**
     * Get route pattern
     *
     * @return string
     */
    public function route(): ?string
    {
        return $this->route;
    }

    /**
     * Set parent route groups
     *
     * @param string[] $groups The parent route groups.
     */
    public function setGroups(array $groups): static
    {
        $this->groups = [];

        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Add parent route group
     *
     * @param string $group The parent route group.
     * @throws InvalidArgumentException If the group is invalid.
     */
    public function addGroup($group): static
    {
        if (!is_string($group)) {
            throw new InvalidArgumentException(
                'Parent route group must be a string.'
            );
        }

        $this->groups[] = $group;

        return $this;
    }

    /**
     * Get parent route groups
     */
    public function groups(): array
    {
        return $this->groups;
    }

    /**
     * Add custom headers
     *
     * @param array $headers The custom headers, in key=>val pairs.
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = [];
        foreach ($headers as $name => $val) {
            $this->addHeader($name, $val);
        }
        return $this;
    }

    /**
     * @param string $name The header name (ex: "Content-Type", "Cache-Control").
     * @param string $val  The header value.
     * @throws InvalidArgumentException If the header name or value is not a string.
     * @return $this
     */
    public function addHeader($name, $val): static
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException(
                'Route header name must be a string.'
            );
        }
        if (!is_string($val)) {
            throw new InvalidArgumentException(
                'Route header value must be a string.'
            );
        }
        $this->headers[$name] = $val;
        return $this;
    }

    /**
     * Get custom route headers.
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Set route view controller classname
     *
     * @param string $controller Route controller name.
     * @throws InvalidArgumentException If the route view controller is not a string.
     */
    public function setController($controller): static
    {
        if (!is_string($controller)) {
            throw new InvalidArgumentException(
                'Route view controller must be a string.'
            );
        }

        $this->controller = $controller;

        return $this;
    }

    /**
     * Get the view controller classname
     *
     * If not set, the `self::ident()` will be used by default.
     *
     * @return string
     */
    public function controller(): ?string
    {
        if ($this->controller === null) {
            return $this->ident();
        }

        return $this->controller;
    }

    /**
     * Set route methods
     *
     * @param string[] $methods The route's supported HTTP methods.
     */
    public function setMethods(array $methods): static
    {
        $this->methods = [];

        foreach ($methods as $method) {
            $this->addMethod($method);
        }

        return $this;
    }

    /**
     * Add route HTTP method.
     *
     * @param string $method The route's supported HTTP method.
     * @throws InvalidArgumentException If the HTTP method is invalid.
     */
    public function addMethod($method): static
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported HTTP method; must be a string, received %s',
                    (get_debug_type($method))
                )
            );
        }

        // According to RFC, methods are defined in uppercase (See RFC 7231)
        $method = strtoupper($method);

        $validHttpMethods = [
            'CONNECT',
            'DELETE',
            'GET',
            'HEAD',
            'OPTIONS',
            'PATCH',
            'POST',
            'PUT',
            'TRACE',
        ];

        if (!in_array($method, $validHttpMethods)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be one of "%s", received "%s"',
                implode('","', $validHttpMethods),
                $method
            ));
        }

        $this->methods[] = $method;

        return $this;
    }

    /**
     * Get route methods
     *
     * @return string[]
     */
    public function methods(): array
    {
        return $this->methods;
    }
}
