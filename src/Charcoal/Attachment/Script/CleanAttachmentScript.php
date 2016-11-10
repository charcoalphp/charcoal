<?php

namespace Charcoal\Attachment\Script;

use \Exception;

// From Pimple
use \Pimple\Container;

// From PSR-7
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// From 'charcoal-app'
use \Charcoal\App\Script\AbstractScript;

// From 'beneroch/charcoal-attachment'
use \Charcoal\Attachment\Object\Attachment;
use \Charcoal\Attachment\Object\Join;

// From 'beneroch/charcoal-utils'
use \Utils\Support\Traits\ConfigAwareTrait;
use \Utils\Support\Traits\ModelAwareTrait;
use \Utils\Support\Interfaces\ConfigAwareInterface;
use \Utils\Support\Interfaces\ModelAwareInterface;

/**
 * Purge unused attachments
 */
class CleanAttachmentScript extends AbstractScript implements
    ConfigAwareInterface,
    ModelAwareInterface
{
    use ConfigAwareTrait;
    use ModelAwareTrait;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    public function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setAppConfig($container['config']);
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * Retrieve the script's supported arguments.
     *
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'hard' => [
                'prefix'      => 'h',
                'longPrefix'  => 'hard',
                'description' => 'Unlink concerned files',
                'noValue'     => true
            ]
        ];

        return array_merge(parent::defaultArguments(), $arguments);
    }

    /**
     * Run the script.
     *
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        try {
            $this->start();
        } catch (Exception $e) {
            $this->climate()->error($e->getMessage());
        }

        return $response;
    }

    /**
     * Execute the prime directive.
     *
     * @return self
     */
    public function start()
    {
        $climate = $this->climate();

        $unlinkFiles = $climate->arguments->defined('hard');

        $climate->underline()->out(
            'Cleaning unused attachments...'
        );

        if ($unlinkFiles) {
            $climate->out('Removing files from the server...');
        }

        $attach = $this->modelFactory()->get(Attachment::class);
        $pivot  = $this->modelFactory()->get(Join::class);
        $loader = $this->collection(Attachment::class);

        $sql = 'SELECT a.* FROM `%attachTable` AS a
                LEFT JOIN `%pivotTable` AS b
                ON a.id = b.attachment_id
                WHERE b.attachment_id IS NULL;';
        $binds = [
            '%attachTable' => $attach->source()->table(),
            '%pivotTable'  => $pivot->source()->table(),
        ];

        $collection = $loader->loadFromQuery(strtr($sql, $binds));
        if (!count($collection)) {
            $climate->out('Nothing to clean in the current database!');
            return $this;
        }

        $deleteSingle   = $climate->confirm('Do you wish to delete this file?');
        $deleteAllFiles = $climate->confirm(
            sprintf(
                'Do you want to do this for all the items in the queue? (%d)'
                count($collection)
            )
        );

        foreach ($collection as $obj) {
            $title = strval($obj->title());
            $data  = [
                'Title'       => $title),
                'Created on'  => $obj->created()->format('Y-m-d H:i:s'),
                'Created by'  => $obj->createdBy(),
                'Modified on' => $obj->lastModified()->format('Y-m-d H:i:s'),
                'Modified by' => $obj->lastModifiedBy(),
                'Type'        => $obj->type()
            ];

            $climate->out(
                sprintf(
                    'Unused attachment #%s %s',
                    $obj->id(),
                    $title
                )
            );

            $climate->json($data)->br();

            // @codingStandardsIgnoreStart
            /*
            // Prompt for single files.
            if (!isset($repeatAction)) {
                $delete = $deleteSingle->confirmed();
            } else {
                $delete = $repeatAction;
            }

            if (!isset($repeatAction)) {
                $repeatAction = $deleteAllFiles->confirmed();
                if ($repeatAction) {
                    $repeatAction = $delete;
                } else {
                    $repeatAction = false;
                }
            }

            if ($repeatAction) {
                $delete = true;
            }

            if (isset($delete) && $delete) {
            }

            unset($delete);
            */
            // @codingStandardsIgnoreEnd
        }

        return $this;
    }
}
