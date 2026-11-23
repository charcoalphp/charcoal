<?php

namespace Charcoal\App\Exception;

use InvalidArgumentException;
use RuntimeException;
use Throwable;
// From PSR-7
use Psr\Http\Message\ResponseInterface;

/**
 * An HTTP exception describes an error (or redirect) that should be rendered
 * as a PSR-7 response instead of being written with `header()` and `exit`.
 *
 * Route handlers catch this exception and convert it with {@see self::toResponse()},
 * which allows the response to flow back through the middleware stack.
 */
class HttpException extends RuntimeException
{
    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var array<string, string|string[]>
     */
    private $headers;

    /**
     * @param integer                   $statusCode The HTTP status code.
     * @param array<string, mixed>      $headers    Additional response headers.
     * @param string                    $message    The exception message.
     * @param Throwable|null            $previous   The previous exception.
     * @throws InvalidArgumentException If the status code is invalid.
     */
    public function __construct(int $statusCode, array $headers = [], string $message = '', ?Throwable $previous = null)
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new InvalidArgumentException(
                sprintf('Invalid HTTP status code "%s".', $statusCode)
            );
        }

        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * @return integer
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Apply the exception's status code and headers to a response.
     *
     * @param  ResponseInterface $response The PSR-7 response.
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $response = $response->withStatus($this->statusCode);

        foreach ($this->headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
