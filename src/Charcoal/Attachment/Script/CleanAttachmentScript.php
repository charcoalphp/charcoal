<?php

namespace Charcoal\Attachment\Script;

use \Exception;

// Pimple dependencies
use \Pimple\Container;

// PSR-7 (http messaging) dependencies
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Module `charcoal-app` dependencies
use \Charcoal\App\Script\AbstractScript;

use \Charcoal\Attachment\Object\Attachment;
use \Charcoal\Attachment\Object\Join;

// Charcoal-utils dependencie
use \Utils\Support\Traits\ConfigAwareTrait;
use \Utils\Support\Interfaces\ConfigAwareInterface;
use \Utils\Support\Traits\ModelAwareTrait;
use \Utils\Support\Interfaces\ModelAwareInterface;


/**
 * Find all strings to be translated in mustache or php files
 */
class CleanAttachmentScript extends AbstractScript implements
    ConfigAwareInterface,
    ModelAwareInterface
{
    use ConfigAwareTrait;
    use ModelAwareTrait;

    /**
     * Dependencies
     * @param Container $container Available dependencies.
     */
    public function setDependencies(Container $container)
    {
        $this->setAppConfig($container['config']);
        $this->setModelFactory($container['model/factory']);
        // $this->setBaseUrl($container['request']->getUri()->getBaseUrl());
        parent::setDependencies($container);
    }

    /**
     * Valid arguments:
     * - file : path/to/csv.csv
     *
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'hard' => [
                'prefix'        => 'h',
                'longPrefix'    => 'hard',
                'description'   => 'Unlink concerned files',
                'noValue'       => true
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        // Unused
        unset($request);

        $climate = $this->climate();

        $unlinkFiles = $climate->arguments->defined('hard');

        $climate->underline()->out(
            'Cleaning unused attachments...'
        );

        if ($unlinkFiles) {
            $climate->out('Removing files from the server...');
        }

        // Main attachment class.
        $proto = $this->modelFactory()->create(Attachment::class);
        $join = $this->modelFactory()->create(Join::class);

        $loader = $this->collection(Attachment::class);

        $q = 'SELECT a.* FROM ' . $proto->source()->table() . ' as a
            LEFT JOIN '. $join->source()->table() . ' as j
            ON a.id = j.attachment_id
            WHERE
            j.attachment_id IS NULL
        ';

        $collection = $loader->loadFromQuery($q);
        if (!count($collection)) {
            $climate->out('Nothing to clean in the current database!');
            return $response;
        }


        $deleteSingle = $climate->input('Do you wish to delete this file? (y/n)');
        $deleteSingle->accept(['Y', 'N']);

        $deleteAllFiles = $climate->input('Do you want to do this for all the items in the queue? ('.count($collection).')  (y/n)');
        $deleteAllFiles->accept(['Y','N']);

        // $acceptedAction

        foreach ($collection as $obj) {
            $data = [
                'Title' => (string)$obj->title(),
                'Created on' => $obj->created()->format('Y-m-d H:i:s'),
                'Created by' => $obj->createdBy(),
                'Modified on' => $obj->lastModified()->format('Y-m-d H:i:s'),
                'Modified by' => $obj->lastModifiedBy(),
                'Type' => $obj->type()
            ];
            $climate->out('Unused attachment #' . $obj->id() . ' ' . (string)$obj->title());
            $climate->json($data)->br();


            // Prompt for single files.
            // if (!isset($repeatAction)) {
            //     $delete = $deleteSingle->prompt();
            // } else {
            //     $delete = $repeatAction;
            // }

            // if (!isset($repeatAction)) {
            //     $repeatAction = $deleteAllFiles->prompt();
            //     if ($repeatAction == 'y') {
            //         $repeatAction = $delete;
            //     } else {
            //         $repeatAction = 'n';
            //     }
            // }

            // if ($repeatAction == 'y') {
            //     $delete = 'y';
            // }

            // if (isset($delete) && $delete == 'y') {

            // }

            // unset($delete);
        }


        return $response;
    }


}
