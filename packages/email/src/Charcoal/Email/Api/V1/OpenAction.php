<?php

declare(strict_types=1);

namespace Charcoal\Email\Api\V1;

use Charcoal\Email\EmailLog;
use Charcoal\Email\Services\Tracker;
use Charcoal\Model\Service\ModelLoader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Track link clicks.
 *
 * Open a link by ID, log the event into the database and redirect to the actual destination.
 */
class OpenAction
{
    /**
     * @var string
     */
    private $emailId;

    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @param string  $emailId Email log ID.
     * @param Tracker $tracker Tracker service.
     */
    public function __construct(string $emailId, Tracker $tracker)
    {
        $this->emailId = $emailId;
        $this->tracker = $tracker;
    }

    /**
     * @param Request  $request  PSR-7 Request.
     * @param Response $response PSR-7 Response.
     * @return Response
     */
    public function __invoke(Request $request, Response $response) : Response
    {
        $ip = $request->getAttribute('client-ip');
        $this->tracker->trackOpen($this->emailId, $ip);

        $response = $response->withHeader('Content-Type', 'image/png');
        $response->getBody()->write($this->getBlankPng());
        return $response;
    }

    /**
     * @return boolean|false|string
     */
    private function getBlankPng()
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
    }
}
