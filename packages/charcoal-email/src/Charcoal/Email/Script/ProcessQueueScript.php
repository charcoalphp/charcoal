<?php

declare(strict_types=1);

namespace Charcoal\Email\Script;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-app'
use Charcoal\App\Script\AbstractScript;
use Charcoal\App\Script\CronScriptInterface;
use Charcoal\App\Script\CronScriptTrait;

// From 'charcoal-factory'
use Charcoal\Factory\FactoryInterface;

// From 'charcoal-email'
use Charcoal\Email\EmailQueueManager;

/**
 * Script: Process Email Queue
 *
 * Can also be used as a cron script.
 */
class ProcessQueueScript extends AbstractScript implements CronScriptInterface
{
    use CronScriptTrait;

    /**
     * @var FactoryInterface
     */
    private $queueItemFactory;

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

        // Lock script; Ensure it can not be run twice at the same time.
        $this->startLock();

        $cli = $this->climate();

        if ($this->dryRun()) {
            $cli->shout('This command does not support --dry-run');
            $cli->br();
            $cli->whisper('Doing nothing');
            return $response;
        }

        $queueManager = $this->makeQueueManager();
        $queueManager->processQueue();

        // Unlock script
        $this->stopLock();

        return $response;
    }

    /**
     * Default script arguments.
     *
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'queue-id' => [
                'longPrefix'   => 'queue-id',
                'description'  => 'The queue to process. If blank, all queues will be processed.',
                'defaultValue' => null,
                'castTo'       => 'string',
            ],
            'rate' => [
                'longPrefix'   => 'rate',
                'description'  => 'Number of items to process per second.',
                'defaultValue' => 50,
                'castTo'       => 'int',
            ],
            'limit' => [
                'longPrefix'   => 'limit',
                'description'  => 'Maximum number of items to process.',
                'defaultValue' => null,
                'castTo'       => 'int',
            ],
            'chunk-size' => [
                'longPrefix'   => 'chunk-size',
                'description'  => 'Number of items to keep in memory at a given time.',
                'defaultValue' => 100,
                'castTo'       => 'int',
            ],
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * Create and prepare the queue manager.
     *
     * @return EmailQueueManager
     */
    protected function makeQueueManager()
    {
        $cli = $this->climate();

        $data = [
            'logger'             => $this->logger,
            'queue_item_factory' => $this->queueItemFactory,
            'chunkSize'          => 100,
        ];

        $rate = $cli->arguments->get('rate');
        if ($rate !== null) {
            $data['rate'] = $rate;
        }

        $limit = $cli->arguments->get('limit');
        if ($limit !== null) {
            $data['limit'] = $limit;
        }

        $chunkSize = $cli->arguments->get('chunk-size');
        if ($chunkSize !== null) {
            $data['chunkSize'] = $chunkSize;
        }

        $class = $this->getQueueManagerClass();
        $queueManager = new $class($data);
        $queueManager->setProcessedCallback($this->getProcessedQueueCallback());

        $queueId = $cli->arguments->get('queue-id');
        if ($queueId !== null) {
            $data['queue_id'] = $queueId;
        }

        return $queueManager;
    }

    /**
     * Retrieve the class name of the queue manager model.
     *
     * @return string
     */
    protected function getQueueManagerClass(): string
    {
        return EmailQueueManager::class;
    }

    /**
     * @return Closure
     */
    protected function getProcessedQueueCallback(): callable
    {
        $climate = $this->climate();

        $callback = function ($success, $failures, $skipped) use ($climate): void {
            if (!empty($success)) {
                $climate->green()->out(sprintf('%s emails were successfully sent.', count($success)));
            }

            if (!empty($failures)) {
                $climate->red()->out(sprintf('%s emails failed to be sent', count($failures)));
            }

            if (!empty($skipped)) {
                $climate->dim()->out(sprintf('%s emails were skipped.', count($skipped)));
            }
        };

        return $callback;
    }

    /**
     * @param  Container $container Pimple DI container.
     * @return void
     */
    protected function setDependencies(Container $container): void
    {
        parent::setDependencies($container);
        $this->setQueueItemFactory($container['model/factory']);
    }

    /**
     * @param  FactoryInterface $factory The factory to create queue items.
     * @return void
     */
    private function setQueueItemFactory(FactoryInterface $factory): void
    {
        $this->queueItemFactory = $factory;
    }
}
