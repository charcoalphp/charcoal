<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

use Charcoal\View\Twig\HelpersInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers for Url.
 */
class UrlHelpers extends AbstractExtension implements
    HelpersInterface
{
    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['baseUrl'])) {
            $this->baseUrl = $data['baseUrl'];
        }
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('baseUrl', [ $this, 'baseUrl' ]),
            new TwigFunction('siteUrl', [ $this, 'baseUrl' ]),
            new TwigFunction('withBaseUrl', [ $this, 'withBaseUrl' ]),
        ];
    }

    /**
     * Render the Twig baseUrl function.
     *
     * @return mixed
     */
    public function baseUrl()
    {
        return ($this->baseUrl ?? '');
    }

    /**
     * Render the Twig baseUrl filter.
     *
     * @param mixed $uri The current uri.
     * @return mixed
     */
    public function withBaseUrl($uri)
    {
        if (null === $this->baseUrl) {
            return '';
        }

        $uri = strval($uri);
        if ($uri === '') {
            $uri = $this->baseUrl->withPath('');
        } else {
            $parts = parse_url($uri);
            if (!isset($parts['scheme'])) {
                if (!in_array($uri[0], [ '/', '#', '?' ])) {
                    $path  = isset($parts['path']) ? $parts['path'] : '';
                    $query = isset($parts['query']) ? $parts['query'] : '';
                    $hash  = isset($parts['fragment']) ? $parts['fragment'] : '';

                    $uri = $this->baseUrl->withPath($path)
                                   ->withQuery($query)
                                   ->withFragment($hash);
                }
            }
        }

        return $uri;
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            static::class => $this,
        ];
    }
}
