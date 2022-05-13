<?php

declare(strict_types=1);

namespace Charcoal\Email\Services;

// From 'locomotivemtl/charcoal-factory'
use Charcoal\Factory\FactoryInterface;

use Charcoal\Email\Email;
use Charcoal\Email\Objects\Link;
use Charcoal\Email\Objects\LinkLog;
use Charcoal\Email\Objects\OpenLog;

/**
 * Tracker Service.
 *
 * Provide methods to
 * - add tracking pixel to email's HTML content;
 * - replace all links in HTML content with tracking links;
 * - track the opening of an email (request to the pixel);
 * - track the clicking of an email link (request to tracking link);
 */
class Tracker
{

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var FactoryInterface
     */
    private $modelFactory;

    /**
     * @param string           $baseUrl      Base URL.
     * @param FactoryInterface $modelFactory Model factory to create link and log objects.
     */
    public function __construct(string $baseUrl, FactoryInterface $modelFactory)
    {
        $this->baseUrl = $baseUrl;
        $this->modelFactory = $modelFactory;
    }

    /**
     * @param Email  $email      Email object to update.
     * @param string $emailLogId Email log ID, to generate image link for.
     * @return void
     */
    public function addOpenTrackingImage(Email &$email, string $emailLogId): void
    {
        $html = $email->msgHtml();
        $regexp = '/(<body.*?>)/i';
        $pixel = sprintf('<img src="%s" alt="" />', $this->baseUrl.'email/v1/open/'.$emailLogId.'.png');
        if (preg_match($regexp, $html) != false) {
            $parts = preg_split($regexp, $html, -1, (PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE));
            $html = $parts[0].$parts[1].$pixel.$parts[2];
        } else {
            $html .= $pixel;
        }
        $email->setMsgHtml($html);
    }

    /**
     * @param Email  $email      Email object to update.
     * @param string $emailLogId Email log ID, to generate links for.
     * @return void
     */
    public function replaceLinksWithTracker(Email &$email, string $emailLogId): void
    {
        $html = $email->msgHtml();

        $callback = function(array $matches) use ($emailLogId): string {
            $linkId  = $this->createLink($emailLogId, $matches[1]);
            $linkUrl = $this->baseUrl.'email/v1/link/'.$linkId;
            return str_replace($matches[1], $linkUrl, $matches[0]);
        };
        $regexp = '/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/';
        $html = preg_replace_callback($regexp, $callback, $html);
        $email->setMsgHtml($html);
    }

    /**
     * @param string      $emailLogId Email log ID, to track.
     * @param string|null $ip         Client IP address.
     * @return void
     */
    public function trackOpen(string $emailLogId, ?string $ip): void
    {
        $log = $this->modelFactory->create(OpenLog::class);
        $log['email'] = $emailLogId;
        $log['ts'] = 'now';
        $log['ip'] = $ip;
        $log->save();
    }

    /**
     * @param string      $linkId Link ID, to track.
     * @param string|null $ip     Client IP address.
     * @return void
     */
    public function trackLink(string $linkId, ?string $ip): void
    {
        $log = $this->modelFactory->create(LinkLog::class);
        $log['link'] = $linkId;
        $log['ts'] = 'now';
        $log['ip'] = $ip;
        $log->save();
    }

    /**
     * @param string $emailLogId Email log ID, to create link for.
     * @param string $url        URL to redirect to.
     * @return string
     */
    private function createLink(string $emailLogId, string $url): string
    {
        $link = $this->modelFactory->create(Link::class);
        $link['email'] = $emailLogId;
        $link['url'] = $url;
        $link->save();
        return $link->id();
    }
}
