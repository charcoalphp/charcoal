<?php

declare(strict_types=1);

namespace Charcoal\Email\Api\V1;

// From 'psr/http-message' (PSR-7)
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
// From 'charcoal/factory'
use Charcoal\Factory\FactoryInterface;
use Charcoal\Email\Objects\Link;
use Charcoal\Email\Services\Tracker;

/**
 * Track link clicks.
 *
 * Open a link by ID, log the event into the database and redirect to the actual destination.
 */
class LinkAction
{
    /**
     * @param string           $linkId       Link ID.
     * @param Tracker          $tracker      Tracker service.
     * @param FactoryInterface $modelFactory Model factory, to create Link objects.
     */
    public function __construct(private readonly string $linkId, private readonly Tracker $tracker, private readonly FactoryInterface $modelFactory)
    {
    }

    /**
     * @param Request  $request  PSR-7 Request.
     * @param Response $response PSR-7 Response.
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $ip = $request->getAttribute('client-ip');
        $this->tracker->trackLink($this->linkId, $ip);

        $link = $this->modelFactory->create(Link::class);
        $link->load($this->linkId);
        if (!$link->id()) {
            return $response->withStatus(404);
        }
        $url = $link['url'];

        return $response
            ->withStatus(301)
            ->withHeader('Location', $url);
    }
}
