<?php

namespace Charcoal\Admin\Action\Elfinder;

use RuntimeException;
use Throwable;
// From PSR-7
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-app'
use Charcoal\App\Helper\CallbackStream;

/**
 * Carries the output produced by elFinder's connector so that it can be
 * rendered as a PSR-7 response instead of being written with `header()` and
 * `exit`.
 *
 * @see \Charcoal\Admin\Action\Elfinder\BufferedConnector
 */
class OutputException extends RuntimeException
{
    /**
     * The raw output data produced by the connector.
     *
     * @var array
     */
    private $data;

    /**
     * The connector-level headers.
     *
     * @var string|string[]|null
     */
    private $headers;

    /**
     * The default content type used when the output defines none.
     *
     * @var string
     */
    private $defaultContentType;

    /**
     * @param array                $data               The connector output data.
     * @param string|string[]|null $headers            The connector-level headers.
     * @param string               $defaultContentType The fallback content type.
     * @param Throwable|null       $previous           The previous exception.
     */
    public function __construct(
        array $data,
        $headers = null,
        string $defaultContentType = 'Content-Type: application/json; charset=utf-8',
        ?Throwable $previous = null
    ) {
        parent::__construct('elFinder connector produced output.', 0, $previous);

        $this->data = $data;
        $this->headers = $headers;
        $this->defaultContentType = $defaultContentType;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Apply the connector output to a response.
     *
     * @param  ResponseInterface $response The PSR-7 response.
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $data = $this->data;

        $response = $this->applyHeaders($response, $this->headers);

        if (!empty($data['header'])) {
            $response = $this->applyHeaders($response, $data['header']);
        }

        if (!$response->hasHeader('Content-Type')) {
            $response = $this->applyHeaders($response, $this->defaultContentType);
        }

        if (isset($data['pointer'])) {
            return $this->createFileResponse($response, $data);
        }

        unset($data['header']);

        if (!empty($data['raw']) && isset($data['error'])) {
            $body = is_array($data['error']) ? implode("\n", $data['error']) : (string)$data['error'];
        } else {
            if (isset($data['debug']['backendErrors'])) {
                $data['debug']['backendErrors'] = array_merge(
                    $data['debug']['backendErrors'],
                    \elFinder::$phpErrors
                );
            }

            $body = (string)json_encode($data);
        }

        $response->getBody()->write($body);

        return $response->withHeader('Content-Length', (string)strlen($body));
    }

    /**
     * Build a response that streams an elFinder file pointer.
     *
     * Basic `Range` support is preserved; more advanced elFinder features
     * (e.g. X-Sendfile) are intentionally ignored because the file is sent
     * by PHP through the PSR-7 response.
     *
     * @param  ResponseInterface $response The PSR-7 response.
     * @param  array             $data     The connector output data.
     * @return ResponseInterface
     */
    private function createFileResponse(ResponseInterface $response, array $data): ResponseInterface
    {
        $pointer = $data['pointer'];
        $info = isset($data['info']) && is_array($data['info']) ? $data['info'] : [];
        $size = isset($info['size']) ? (int)$info['size'] : null;

        $start = 0;
        $length = $size;
        $satisfiable = true;

        if ($size !== null && !empty($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\s*(\d*)-(\d*)/i', $_SERVER['HTTP_RANGE'], $matches)) {
                if ($matches[1] === '' && $matches[2] !== '') {
                    $start = max(0, ($size - (int)$matches[2]));
                    $length = ($size - $start);
                } else {
                    $start = (int)$matches[1];
                    $end = ($matches[2] !== '') ? min((int)$matches[2], ($size - 1)) : ($size - 1);

                    if ($end >= $start) {
                        $length = ($end - $start + 1);
                    }
                }

                if ($length === null || $length <= 0 || $start >= $size) {
                    $satisfiable = false;
                } elseif ($start > 0 || $length !== $size) {
                    $response = $response
                        ->withStatus(206)
                        ->withHeader(
                            'Content-Range',
                            sprintf('bytes %d-%d/%d', $start, ($start + $length - 1), $size)
                        );
                }
            }
        }

        $volume = isset($data['volume']) ? $data['volume'] : null;
        $hash = isset($info['hash']) ? $info['hash'] : null;

        if (!$satisfiable) {
            $this->closePointer($pointer, $volume, $hash);

            return $response
                ->withStatus(416)
                ->withHeader('Content-Range', 'bytes */' . $size)
                ->withHeader('Content-Length', '0');
        }

        $response = $response->withHeader('Accept-Ranges', $size === null ? 'none' : 'bytes');

        if ($length !== null) {
            $response = $response->withHeader('Content-Length', (string)$length);
        }

        $seekable = $this->isSeekable($pointer);

        $stream = new CallbackStream(function () use ($pointer, $volume, $hash, $start, $length, $seekable) {
            if ($seekable && $start > 0) {
                fseek($pointer, $start);
            }

            $output = stream_get_contents($pointer, $length);

            $this->closePointer($pointer, $volume, $hash);

            return $output;
        });

        return $response->withBody($stream);
    }

    /**
     * Close an elFinder file pointer.
     *
     * @param  mixed       $pointer The stream resource.
     * @param  mixed       $volume  The elFinder volume, if any.
     * @param  string|null $hash    The file hash.
     * @return void
     */
    private function closePointer($pointer, $volume = null, ?string $hash = null): void
    {
        if ($volume !== null && method_exists($volume, 'close')) {
            $volume->close($pointer, $hash);
            return;
        }

        if (is_resource($pointer)) {
            fclose($pointer);
        }
    }

    /**
     * @param  mixed $pointer The stream resource.
     * @return boolean
     */
    private function isSeekable($pointer): bool
    {
        if (!is_resource($pointer)) {
            return false;
        }

        $meta = stream_get_meta_data($pointer);

        return !empty($meta['seekable']);
    }

    /**
     * Apply one or more raw HTTP header lines to a response.
     *
     * @param  ResponseInterface  $response The PSR-7 response.
     * @param  string|string[]|null $headers The header line(s).
     * @return ResponseInterface
     */
    private function applyHeaders(ResponseInterface $response, $headers): ResponseInterface
    {
        if (empty($headers)) {
            return $response;
        }

        foreach ((array)$headers as $header) {
            if (!is_string($header) || trim($header) === '') {
                continue;
            }

            if (preg_match('#^HTTP/\d(?:\.\d)?\s+(\d{3})#i', $header, $matches)) {
                $response = $response->withStatus((int)$matches[1]);
                continue;
            }

            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $response = $response->withHeader(trim($parts[0]), trim($parts[1]));
            }
        }

        return $response;
    }
}
