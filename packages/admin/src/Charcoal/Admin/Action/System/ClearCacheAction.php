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

/**
 * Empty the entire cache pool of items.
 */
class ClearCacheAction extends AbstractCacheAction
{
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

        $result  = false;
        $message = null;

        if ($cacheType === 'global') {
            $result = $this->clearGlobalCache();

            if ($result) {
                $message = $translator->translate('Cache cleared successfully.');
            } else {
                $message = $translator->translate('Failed to clear cache.');
            }
        } elseif ($cacheType === 'pages') {
            $result = $this->clearPagesCache();

            if ($result) {
                $message = $translator->translate('Pages cache cleared successfully.');
            } else {
                $message = $translator->translate('Failed to clear pages cache.');
            }
        } elseif ($cacheType === 'objects') {
            $result = $this->clearObjectsCache();

            if ($result) {
                $message = $translator->translate('Objects cache cleared successfully.');
            } else {
                $message = $translator->translate('Failed to clear objects cache.');
            }
        } elseif ($cacheType === 'twig') {
            $result = $this->clearTwigCache();

            if ($result) {
                $message = $translator->translate('Twig cache cleared successfully.');
            } else {
                $message = $translator->translate('Failed to clear Twig cache.');
            }
        } elseif ($cacheType === 'mustache') {
            $result = $this->clearMustacheCache();

            if ($result) {
                $message = $translator->translate('Mustache cache cleared successfully.');
            } else {
                $message = $translator->translate('Failed to clear Mustache cache.');
            }
        } elseif ($cacheType === 'cloudflare') {
            $result = $this->clearCloudflareCache($message);

            if ($message === null) {
                if ($result) {
                    $message = $translator->translate('Cloudflare cache purged successfully.');
                } else {
                    $message = $translator->translate('Failed to purge Cloudflare cache.');
                }
            }
        } elseif ($cacheType === 'item') {
            $message = $translator->translate('Deleting cache items is unsupported, for now.');
        } else {
            $this->addFeedback('error', $translator->translate(sprintf('Invalid cache type "%s"', $cacheType)));
            $this->setSuccess(false);
            return $response->withStatus(400);
        }

        $this->setSuccess($result);

        if ($result) {
            $this->addFeedback('success', $message);
            return $response;
        } else {
            $this->addFeedback('error', $message);
            return $response->withStatus(500);
        }
    }

    /**
     * Clear the global cache.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearGlobalCache()
    {
        $cache  = $this->cachePool();
        $result = $cache->clear();
        return $result;
    }

    /**
     * Clear the pages cache.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearPagesCache()
    {
        $cache  = $this->cachePool();
        $result = $cache->deleteItems([ 'request', 'template' ]);
        return $result;
    }

    /**
     * Clear the objects cache.
     *
     * @return boolean TRUE if cache type cleared, FALSE otherwise.
     */
    private function clearObjectsCache()
    {
        $cache  = $this->cachePool();
        $result = $cache->deleteItems([ 'object', 'metadata' ]);
        return $result;
    }

    private function clearTwigCache(): bool
    {
        $engine = $this->getTwigEngine();
        if (!$engine) {
            return true;
        }

        $defaultCachePath = realpath($engine->cache());
        $cachePath = $defaultCachePath
            ? $defaultCachePath
            : realpath($this->appConfig['publicPath'] . DIRECTORY_SEPARATOR . $engine->cache());
        if (!is_dir($cachePath)) {
            return false;
        }
        $this->rrmdir($cachePath);
        return true;
    }

    private function clearMustacheCache(): bool
    {
        $engine = $this->getMustacheEngine();
        if (!$engine) {
            return true;
        }

        $defaultCachePath = realpath($engine->cache());
        $cachePath = $defaultCachePath
            ? $defaultCachePath
            : realpath($this->appConfig['publicPath'] . DIRECTORY_SEPARATOR . $engine->cache());
        if (!is_dir($cachePath)) {
            return false;
        }
        $this->rrmdir($cachePath);
        return true;
    }

    /**
     * Purge the Cloudflare edge cache for the configured zone.
     *
     * Expects `apis.cloudflare.zone_id` and `apis.cloudflare.api_token`
     * to be defined in the application or admin configset, the same way
     * `apis.google.recaptcha.*` is configured for reCAPTCHA validation.
     *
     * @param  string|null $message Reference; set to a translated feedback
     *     message when a more specific one than the generic success/failure
     *     text is available (e.g. "not configured" or Cloudflare's own error).
     * @return boolean TRUE if the Cloudflare cache was purged, FALSE otherwise.
     */
    private function clearCloudflareCache(&$message = null): bool
    {
        $translator = $this->translator();

        $zoneId   = $this->apiConfig('cloudflare.zone_id');
        $apiToken = $this->apiConfig('cloudflare.api_token');

        if (empty($zoneId) || empty($apiToken)) {
            $message = $translator->translate('Cloudflare is not configured.');
            return false;
        }

        $client = new GuzzleClient();

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
                return true;
            }

            if (!empty($body['errors'][0]['message'])) {
                $message = $body['errors'][0]['message'];
            }

            return false;
        } catch (GuzzleException $e) {
            $message = $translator->translate('Failed to reach Cloudflare: ') . $e->getMessage();
            return false;
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
