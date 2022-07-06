<?php

namespace Charcoal\View\Twig;

// From 'charcoal-view'
use Charcoal\View\Twig\HelpersInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers for Url.
 */
class UrlHelpers extends AbstractExtension
    implements HelpersInterface
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

    public function getFunctions()
    {
        return [
            new TwigFunction('baseUrl', [$this, 'baseUrl']),
            new TwigFunction('siteUrl', [$this, 'baseUrl']),
            new TwigFunction('withBaseUrl', [$this, 'withBaseUrl']),
        ];
    }

    /**
     * Render the Twig baseUrl function.
     *
     * @return string
     */
    public function baseUrl()
    {
        if (null === $this->baseUrl) {
            return '';
        }

        return $this->baseUrl;
    }

    /**
     * Render the Twig baseUrl filter.
     *
     * @return string
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
    public function toArray()
    {
        return [
            'Charcoal\View\Twig\UrlHelpers' => $this,
        ];
    }
}
