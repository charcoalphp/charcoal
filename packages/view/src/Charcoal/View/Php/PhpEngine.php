<?php

declare(strict_types=1);

namespace Charcoal\View\Php;

use Charcoal\View\AbstractEngine;
use JsonException;
use UnexpectedValueException;

/**
 * PHP view rendering engine
 */
class PhpEngine extends AbstractEngine
{
    /**
     * @return string
     */
    public function type(): string
    {
        return 'php';
    }

    /**
     * @param string $templateString The template string to render.
     * @param mixed  $context        The rendering context.
     * @return string The rendered template string.
     */
    public function renderTemplate(string $templateString, $context): string
    {
        try {
            $arrayContext = json_decode(json_encode($context, JSON_THROW_ON_ERROR), true);
        } catch (JsonException $e) {
            if (strlen($templateString) > 30) {
                // Truncate the string to avoid polluting the logs with a long template.
                $templateString = substr($templateString, 0, 29) . '…';
            }

            throw new UnexpectedValueException(
                sprintf('PHP cannot render template [%s]: %s', $templateString, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        // Prevents leaking global variable by forcing anonymous scope
        $render = function ($templateString, array $context) {
            extract($context);
            return eval('?>' . $templateString);
        };

        ob_start();
        $render($templateString, $arrayContext);
        $output = ob_get_clean();

        return $output;
    }
}
