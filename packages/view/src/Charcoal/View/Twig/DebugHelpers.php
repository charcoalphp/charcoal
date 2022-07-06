<?php

namespace Charcoal\View\Twig;

// From 'charcoal-view'
use Charcoal\View\Twig\HelpersInterface;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers for Url.
 */
class DebugHelpers extends AbstractExtension
    implements HelpersInterface
{
    /**
     * @param array $data Class Dependencies.
     */
    public function __construct(array $data = null)
    {
        if (isset($data['config'])) {
            $this->config = $data['config'];
        }
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('debug', [$this, 'isDebug']),
            new TwigFunction('isDebug', [$this, 'isDebug']),
        ];
    }

    /**
     * Render the Twig baseUrl function.
     *
     * @return string
     */
    public function isDebug()
    {
        if (null === $this->config) {
            return false;
        }

        return ($this->config['debug'] || $this->config['dev_mode']);
    }

    /**
     * Retrieve the helpers.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'Charcoal\View\Twig\DebugHelpers' => $this,
        ];
    }
}
