<?php

declare(strict_types=1);

namespace Charcoal\Email\Script;

// From 'psr/http-message' (PSR-7)
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From 'pimple/pimple'
use Pimple\Container;

// From 'locomotivemtl/charcoal-app'
use Charcoal\App\Script\AbstractScript;
use Charcoal\App\Script\CronScriptInterface;
use Charcoal\App\Script\CronScriptTrait;

// From 'locomotivemtl/charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// Local dependencies
use Charcoal\Email\EmailQueueManager;

/**
 * Process Email Queue script.
 *
 * Can also be used as a cron script.
 */
class ProcessQueueScript extends AbstractScript implements CronScriptInterface
{
    use CronScriptTrait;

    /**
     * @var FactoryInterface $queueItemFactory
     */
    private $queueItemFactory;

    /**
     * A copy of all sent messages.
     *
     * @var array $sent
     */
    private $sent;

    /**
     * Process all messages currently in queue.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Unused parameter
        unset($request);

        // Lock script, to ensure it can not be run twice at the same time (before previous instance is done).
        $this->startLock();

        $climate = $this->climate();

        $processedCallback = function ($success, $failures, $skipped) use ($climate): void {
            if (!empty($success)) {
                $climate->green()->out(sprintf('%s emails were successfully sent.', count($success)));
            }

            if (!empty($failures)) {
                $climate->red()->out(sprintf('%s emails were not successfully sent', count($failures)));
            }

            if (!empty($skipped)) {
                $climate->dim()->out(sprintf('%s emails were skipped.', count($skipped)));
            }
        };

        $queueManager = new EmailQueueManager([
            'logger'             => $this->logger,
            'queue_item_factory' => $this->queueItemFactory,
            'chunkSize'          => 100
        ]);
        $queueManager->setProcessedCallback($processedCallback);
        $queueManager->processQueue();

        $this->stopLock();

        return $response;
    }

    /**
     * @param Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container): void
    {
        parent::setDependencies($container);
        $this->setQueueItemFactory($container['model/factory']);
    }

    /**
     * @param FactoryInterface $factory The factory to create queue items.
     * @return void
     */
    private function setQueueItemFactory(FactoryInterface $factory): void
    {
        $this->queueItemFactory = $factory;
    }
}
