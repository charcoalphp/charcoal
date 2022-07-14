<?php

declare(strict_types=1);

namespace Charcoal\View\Twig;

use Charcoal\View\Twig\HelpersInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig helpers for Debug.
 */
class DebugHelpers extends AbstractExtension implements
    HelpersInterface
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('debug', [ $this, 'isDebug' ]),
            new TwigFunction('isDebug', [ $this, 'isDebug' ]),
        ];
    }

    public function isDebug(): bool
    {
        return ($this->config['debug'] ?? false);
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
