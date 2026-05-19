<?php

namespace Charcoal\App\Handler;

use Throwable;
use UnexpectedValueException;
// From PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-app'
use Charcoal\App\Handler\AbstractError;

/**
 * Error Handler for PHP 7+ Throwables
 *
 * Enhanced version of {@see \Slim\Handlers\PhpError}.
 */
class PhpError extends AbstractError
{
    public const DEFAULT_PARTIAL = 'charcoal/app/handler/500';

    /**
     * Invoke Error Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  Throwable              $error    The caught Throwable object.
     * @throws UnexpectedValueException If the content type could not be determined.
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Throwable $error
    ) {
        $this->setHttpRequest($request);
        $this->setThrown($error);

        $contentType = $this->determineContentType($request);
        $output = match ($contentType) {
            'application/json' => $this->renderJsonOutput(),
            'text/xml', 'application/xml' => $this->renderXmlOutput(),
            'text/html' => $this->renderHtmlOutput(),
            'text/plain' => $this->renderPlainOutput(),
            default => throw new UnexpectedValueException(sprintf(
                'Cannot render unknown content type: %s',
                $contentType
            )),
        };

        $this->writeToErrorLog($error);

        return $this->respondWith(
            $response->withStatus(500),
            $contentType,
            $output
        );
    }

    /**
     * Render Text Error
     */
    protected function renderPlainOutput(): string
    {
        $message = $this->renderTextMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render JSON Error
     */
    protected function renderJsonOutput(): string
    {
        $message = $this->renderJsonMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render XML Error
     */
    protected function renderXmlOutput(): string
    {
        $message = $this->renderXmlMessage($this->getThrown());

        return $this->renderTemplate($message);
    }

    /**
     * Render HTML Error
     *
     * @return string
     */
    protected function renderHtmlOutput()
    {
        return $this->renderHtmlTemplate();
    }
}
