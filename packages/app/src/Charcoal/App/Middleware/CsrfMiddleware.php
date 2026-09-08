<?php

namespace Charcoal\App\Middleware;

use InvalidArgumentException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'slim/csrf'
use Slim\Csrf\Guard;

/**
 * The CSRF middleware validates a Slim-CSRF token pair on state-changing
 * requests, scoped to a configurable set of paths.
 *
 * Unlike a blanket CSRF check, this middleware only touches the session (and
 * therefore only affects cacheability / cookies) for requests matching
 * `included_path`, so it can be enabled app-wide without forcing a session on
 * every page. Token issuance for the pages that render a protected form is a
 * separate concern — see the CSRF-aware template helpers that use the same
 * shared {@see Guard} instance.
 */
class CsrfMiddleware
{
    /**
     * @var Guard
     */
    private $guard;

    /**
     * @var string[]
     */
    private $includedPath;

    /**
     * @var string[]
     */
    private $excludedPath;

    /**
     * @var string
     */
    private $failureMessage;

    /**
     * @var array
     */
    private $failureBody;

    /**
     * @param  array $data Constructor dependencies and options.
     * @throws InvalidArgumentException If the required 'guard' dependency is missing.
     */
    public function __construct(array $data)
    {
        if (!isset($data['guard']) || !($data['guard'] instanceof Guard)) {
            throw new InvalidArgumentException(
                'CsrfMiddleware requires a "guard" dependency (instance of Slim\Csrf\Guard).'
            );
        }

        $data = array_merge($this->defaults(), $data);

        $this->guard = $data['guard'];
        $this->includedPath = $this->assertValidPatterns($data['included_path'], 'included_path');
        $this->excludedPath = $this->assertValidPatterns($data['excluded_path'], 'excluded_path');
        $this->failureMessage = $data['failure_message'];
        $this->failureBody = $data['failure_body'];

        $this->guard->setFailureCallable([$this, 'handleFailure']);
    }

    /**
     * Default middleware options.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'included_path'   => [],
            'excluded_path'   => [],
            'failure_message' => 'Invalid or expired form token. Please refresh the page and try again.',
            'failure_body'    => [
                'success' => false,
                'message' => '{{message}}',
            ],
        ];
    }

    /**
     * @param RequestInterface  $request  The PSR-7 HTTP request.
     * @param ResponseInterface $response The PSR-7 HTTP response.
     * @param callable          $next     The next middleware callable in the stack.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $path = $request->getUri()->getPath();

        if (!$this->pathIncluded($path) || $this->pathExcluded($path)) {
            return $next($request, $response);
        }

        if (!session_id()) {
            session_start();
        }

        $guard = $this->guard;

        return $guard($request, $response, $next);
    }

    /**
     * Slim-CSRF failure callable.
     *
     * Responds with a JSON body built from the configured `failure_body`
     * template (default: {"success": false, "message": "..."}), so
     * consuming applications can parse a CSRF rejection into whatever
     * response shape they already use elsewhere — e.g. a `feedbacks` array —
     * rather than Slim-CSRF's default plain-text body or a single hardcoded
     * shape.
     *
     * @param RequestInterface  $request  The PSR-7 HTTP request.
     * @param ResponseInterface $response The PSR-7 HTTP response.
     * @param callable          $next     The next middleware callable (unused; the chain stops here).
     * @return ResponseInterface
     */
    public function handleFailure(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $response->getBody()->write(json_encode($this->resolveFailureBody($this->failureBody)));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Recursively substitutes the literal placeholder `{{message}}` with the
     * configured failure message, anywhere it appears in the given value.
     *
     * @param  mixed $value A `failure_body` template value (array or scalar).
     * @return mixed
     */
    private function resolveFailureBody($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'resolveFailureBody'], $value);
        }

        if ($value === '{{message}}') {
            return $this->failureMessage;
        }

        return $value;
    }

    /**
     * @param  string $path The request path to check.
     * @return boolean
     */
    private function pathIncluded(string $path): bool
    {
        if (empty($this->includedPath)) {
            return true;
        }

        return $this->pathMatches($path, $this->includedPath);
    }

    /**
     * @param  string $path The request path to check.
     * @return boolean
     */
    private function pathExcluded(string $path): bool
    {
        if (empty($this->excludedPath)) {
            return false;
        }

        return $this->pathMatches($path, $this->excludedPath);
    }

    /**
     * @param  string   $path     The request path to check.
     * @param  string[] $patterns The regular expressions to match against.
     * @return boolean
     */
    private function pathMatches(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match('#' . $pattern . '#', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates each pattern eagerly, at construction time, rather than
     * letting a malformed regex silently degrade `preg_match()` to a "no
     * match" result at request time — which, for `included_path`, would mean
     * a typo in configuration silently disables CSRF protection instead of
     * failing loudly.
     *
     * @param  string[] $patterns The regular expressions to validate.
     * @param  string   $option   The option name, for the exception message.
     * @throws InvalidArgumentException If a pattern is not a valid regular expression.
     * @return string[] The validated patterns.
     */
    private function assertValidPatterns(array $patterns, string $option): array
    {
        foreach ($patterns as $pattern) {
            if (@preg_match('#' . $pattern . '#', '') === false) {
                throw new InvalidArgumentException(
                    sprintf('CsrfMiddleware "%s" contains an invalid pattern: "%s".', $option, $pattern)
                );
            }
        }

        return $patterns;
    }
}
