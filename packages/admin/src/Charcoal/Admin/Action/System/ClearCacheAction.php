<?php

namespace Charcoal\Admin\Action\System;

// From 'guzzlehttp/guzzle'
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Action\System\AbstractCacheAction;
use Charcoal\View\AbstractEngine;

/**
 * Clear one or more caches.
 *
 * Supported cache types:
 *
 * - `app`: The application cache pool (pages, objects, metadata, etc.).
 * - `pages`: The pages subset of the application cache.
 * - `objects`: The objects and metadata subset of the application cache.
 * - `templates`: The compiled Twig and Mustache templates.
 * - `twig`, `mustache`: The compiled templates of a single view engine.
 * - `cloudflare`: The Cloudflare edge cache (if configured).
 * - `global` (or `all`): All of the above.
 */
class ClearCacheAction extends AbstractCacheAction
{
    /**
     * Human-readable list of what was cleared.
     *
     * @var string[]
     */
    private $cleared = [];

    /**
     * Human-readable list of what could not be cleared.
     *
     * @var string[]
     */
    private $errors = [];

    /**
     * @todo   Implement support for deleting a specific cache item.
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $translator = $this->translator();

        $cacheType = $request->getParam('cache_type');
        if (!is_string($cacheType) || empty($cacheType)) {
            $this->addFeedback('error', $translator->translate('Cache type not defined.'));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        switch ($cacheType) {
            case 'global':
            case 'all':
                $this->clearAppCache();
                $this->clearTemplatesCache();
                if ($this->isCloudflareConfigured()) {
                    $this->clearCloudflareCache();
                }
                $successMessage = $translator->translate('All caches cleared successfully.');
                $errorMessage   = $translator->translate('Some caches could not be cleared.');
                break;

            case 'app':
                $this->clearAppCache();
                $successMessage = $translator->translate('Application cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear application cache.');
                break;

            case 'pages':
                $this->clearPagesCache();
                $successMessage = $translator->translate('Pages cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear pages cache.');
                break;

            case 'objects':
                $this->clearObjectsCache();
                $successMessage = $translator->translate('Objects cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear objects cache.');
                break;

            case 'templates':
                $this->clearTemplatesCache();
                $successMessage = $translator->translate('Templates cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear templates cache.');
                break;

            case 'twig':
                $this->clearTwigCache();
                $successMessage = $translator->translate('Twig cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear Twig cache.');
                break;

            case 'mustache':
                $this->clearMustacheCache();
                $successMessage = $translator->translate('Mustache cache cleared successfully.');
                $errorMessage   = $translator->translate('Failed to clear Mustache cache.');
                break;

            case 'cloudflare':
                $this->clearCloudflareCache();
                $successMessage = $translator->translate('Cloudflare cache purged successfully.');
                $errorMessage   = $translator->translate('Failed to purge Cloudflare cache.');
                break;

            case 'item':
                $this->addFeedback('error', $translator->translate('Deleting cache items is unsupported, for now.'));
                $this->setSuccess(false);
                return $response->withStatus(500);

            default:
                $this->addFeedback('error', sprintf($translator->translate('Invalid cache type "%s"'), $cacheType));
                $this->setSuccess(false);
                return $response->withStatus(400);
        }

        $result  = empty($this->errors);
        $message = $result ? $successMessage : $errorMessage;

        $this->logCacheEvent($cacheType, $result);

        $this->setSuccess($result);
        $this->addFeedback(($result ? 'success' : 'error'), $this->formatFeedback($message));

        return $result ? $response : $response->withStatus(500);
    }

    /**
     * Build the feedback message with the list of what was (and wasn't) cleared.
     *
     * @param  string $message The summary message.
     * @return string
     */
    private function formatFeedback(string $message): string
    {
        $translator = $this->translator();

        $html = '<p>' . $this->escape($message) . '</p>';

        if (!empty($this->cleared)) {
            $html .= '<p>' . $this->escape($translator->translate('The following caches were cleared:')) . '</p>';
            $html .= $this->formatList($this->cleared);
        }

        if (!empty($this->errors)) {
            $html .= '<p>' . $this->escape($translator->translate('The following caches could not be cleared:')) . '</p>';
            $html .= $this->formatList($this->errors);
        }

        return $html;
    }

    /**
     * @param  string[] $items The list items.
     * @return string
     */
    private function formatList(array $items): string
    {
        return '<ul>' . implode('', array_map(function ($item) {
            return '<li>' . $this->escape($item) . '</li>';
        }, $items)) . '</ul>';
    }

    /**
     * @param  string $str The string to escape.
     * @return string
     */
    private function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES);
    }

    /**
     * Log the cache clearing event as a warning, since it affects the whole site.
     *
     * @param  string  $cacheType The requested cache type.
     * @param  boolean $result    Whether all caches were cleared.
     * @return void
     */
    private function logCacheEvent(string $cacheType, bool $result): void
    {
        $user = $this->getAuthenticatedUser();

        $this->logger->warning(
            sprintf(
                '[Admin] Cache clear "%s" %s by %s. Cleared: %s. Errors: %s.',
                $cacheType,
                ($result ? 'succeeded' : 'failed'),
                ($user ? sprintf('%s (%s)', $user->getEmail(), $user->id()) : 'unknown user'),
                ($this->cleared ? implode('; ', $this->cleared) : 'none'),
                ($this->errors ? implode('; ', $this->errors) : 'none')
            ),
            [
                'cache_type' => $cacheType,
                'success'    => $result,
                'cleared'    => $this->cleared,
                'errors'     => $this->errors,
                'user'       => ($user ? $user->id() : null),
            ]
        );
    }

    /**
     * Record the outcome of clearing a cache.
     *
     * @param  boolean     $result Whether the cache was cleared.
     * @param  string      $label  Human-readable description of the cache.
     * @param  string|null $error  Optional error detail.
     * @return boolean
     */
    private function report(bool $result, string $label, ?string $error = null): bool
    {
        if ($result) {
            $this->cleared[] = $label;
        } else {
            $this->errors[] = ($error ? sprintf('%s: %s', $label, $error) : $label);
        }

        return $result;
    }

    /**
     * Clear the entire application cache pool.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearAppCache(): bool
    {
        return $this->report(
            $this->cachePool()->clear(),
            $this->translator()->translate('Application cache (pages, objects, metadata and all other items)')
        );
    }

    /**
     * Clear the pages cache.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearPagesCache(): bool
    {
        return $this->report(
            $this->cachePool()->deleteItems([ 'request', 'template' ]),
            $this->translator()->translate('Pages cache (request and template items)')
        );
    }

    /**
     * Clear the objects cache.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearObjectsCache(): bool
    {
        return $this->report(
            $this->cachePool()->deleteItems([ 'object', 'metadata' ]),
            $this->translator()->translate('Objects cache (object and metadata items)')
        );
    }

    /**
     * Clear the compiled templates of all available view engines.
     *
     * @return boolean TRUE if all template caches were cleared, FALSE otherwise.
     */
    private function clearTemplatesCache(): bool
    {
        $twig     = $this->clearTwigCache();
        $mustache = $this->clearMustacheCache();

        return ($twig && $mustache);
    }

    private function clearTwigCache(): bool
    {
        return $this->clearViewCache(
            $this->getTwigEngine(),
            $this->translator()->translate('Twig compiled templates')
        );
    }

    private function clearMustacheCache(): bool
    {
        return $this->clearViewCache(
            $this->getMustacheEngine(),
            $this->translator()->translate('Mustache compiled templates')
        );
    }

    /**
     * Delete the compiled templates of a view engine.
     *
     * Skipped silently if the engine is unavailable. A missing cache folder
     * is not an error: there is simply nothing to clear.
     *
     * @param  AbstractEngine|null  $engine The view engine.
     * @param  string               $label  Human-readable description of the cache.
     * @return boolean TRUE if cache cleared (or nothing to clear), FALSE otherwise.
     */
    private function clearViewCache(?AbstractEngine $engine, string $label): bool
    {
        if (!$engine) {
            return true;
        }

        $defaultCachePath = realpath($engine->cache());
        $cachePath = $defaultCachePath
            ? $defaultCachePath
            : realpath($this->appConfig['publicPath'] . DIRECTORY_SEPARATOR . $engine->cache());
        if (!$cachePath || !is_dir($cachePath)) {
            return $this->report(true, sprintf(
                $this->translator()->translate('%s (nothing to clear)'),
                $label
            ));
        }

        $this->rrmdir($cachePath);
        return $this->report(true, $label);
    }

    /**
     * Determine if the Cloudflare API is configured for this project.
     *
     * @return boolean
     */
    private function isCloudflareConfigured(): bool
    {
        return !empty($this->apiConfig('cloudflare.zone_id')) && !empty($this->apiConfig('cloudflare.api_token'));
    }

    /**
     * Purge the Cloudflare edge cache for the configured zone.
     *
     * Expects `apis.cloudflare.zone_id` and `apis.cloudflare.api_token`
     * to be defined in the application or admin configset, the same way
     * `apis.google.recaptcha.*` is configured for reCAPTCHA validation.
     *
     * @return boolean TRUE if the Cloudflare cache was purged, FALSE otherwise.
     */
    private function clearCloudflareCache(): bool
    {
        $translator = $this->translator();

        $label = $translator->translate('Cloudflare edge cache');

        if (!$this->isCloudflareConfigured()) {
            return $this->report(false, $label, $translator->translate('Cloudflare is not configured.'));
        }

        $zoneId   = $this->apiConfig('cloudflare.zone_id');
        $apiToken = $this->apiConfig('cloudflare.api_token');

        $client = new GuzzleClient([
            'timeout' => 5,
        ]);

        try {
            $res = $client->request(
                'POST',
                sprintf('https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $zoneId),
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $apiToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'purge_everything' => true,
                    ],
                ]
            );

            $body = json_decode((string)$res->getBody(), true);

            if (!empty($body['success'])) {
                $label = sprintf(
                    $translator->translate('Cloudflare edge cache for zone %s (purge ID: %s): all HTML pages, static assets and other cached files'),
                    $zoneId,
                    ($body['result']['id'] ?? '?')
                );
                return $this->report(true, $label);
            }

            return $this->report(false, $label, ($body['errors'][0]['message'] ?? null));
        } catch (GuzzleException $e) {
            return $this->report(
                false,
                $label,
                $translator->translate('Failed to reach Cloudflare: ') . $e->getMessage()
            );
        }
    }

    private function rrmdir(string $dir, bool $deleteCurrentFolder = false): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }

                if (filetype($dir . DIRECTORY_SEPARATOR . $object) === 'dir') {
                    $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object, true);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }

            if ($deleteCurrentFolder) {
                rmdir($dir);
            }
        }
    }
}
