<?php

namespace Charcoal\Attachment\Script;

use PDO;
use Exception;
use InvalidArgumentException;
// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
// From 'charcoal-app'
use Charcoal\App\Script\AbstractScript;
// From 'charcoal/attachment'
use Charcoal\Attachment\Interfaces\AttachableInterface;
use Charcoal\Attachment\Object\Attachment;
use Charcoal\Attachment\Object\Join;
// From 'charcoal/utils'
use Utils\Support\Traits\ConfigAwareTrait;
use Utils\Support\Traits\ModelAwareTrait;
use Utils\Support\Interfaces\ConfigAwareInterface;
use Utils\Support\Interfaces\ModelAwareInterface;

/**
 * Remove unassociated attachments
 */
class CleanupScript extends AbstractScript implements
    ConfigAwareInterface,
    ModelAwareInterface
{
    use ConfigAwareTrait;
    use ModelAwareTrait;

    /**
     * Store the last action.
     *
     * @var string|null
     */
    protected $action;

    /**
     * Whether to repeat the last action.
     *
     * @var integer
     */
    protected $repeat = 0;

    /**
     * The number of unused attachments.
     *
     * @var integer
     */
    protected $total = 0;

    /**
     * Count the processed attachments.
     *
     * @var integer
     */
    protected $processed = 0;

    /**
     * Count the remaining attachments to process.
     *
     * @var integer
     */
    protected $remainder = 0;

    /**
     * Count the successful attachment deletions.
     *
     * @var integer
     */
    protected $pruned = 0;

    /**
     * Count the failed attachment deletions.
     *
     * @var integer
     */
    protected $failed = 0;

    /**
     * Count the ignored attachments.
     *
     * @var integer
     */
    protected $ignored = 0;

    /**
     * Left-side indentation.
     *
     * @var string
     */
    protected $indent = '';

    /**
     * Store of messages to output at the end.
     *
     * @var array
     */
    protected $messages = [];

    /**
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->setDescription(
            'The <underline>attachment/prune</underline> script ' .
            'removes all unassociated attachments.'
        );
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setAppConfig($container['config']);
        $this->setModelFactory($container['model/factory']);
    }

    /**
     * @return boolean
     */
    public function interactive()
    {
        return true;
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

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        $arguments['interactive']['defaultValue'] = true;

        return $arguments;
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
        $cli = $this->climate();

        $cli->br();
        $cli->bold()->underline()->out('Cleanup unused attachments & relationships');
        $cli->br();

        # $this->pruneRelationships();
        $this->pruneAttachments();

        return $this;
    }

    /**
     * Prune relationships of dead objects.
     *
     * @return self
     */
    protected function pruneRelationships()
    {
        $cli   = $this->climate();
        $ask   = $this->interactive();
        $dry   = $this->dryRun();
        $verb  = $this->verbose();
        $mucho = ($dry || $verb);

        $attach = $this->modelFactory()->get(Attachment::class);
        $pivot  = $this->modelFactory()->get(Join::class);
        $source = $attach->source();
        $db     = $source->db();

        $defaultBinds = [
            '%pivotTable' => $pivot->source()->table(),
            '%sourceType' => 'object_type',
            '%sourceId'   => 'object_id',
        ];

        $sql = 'SELECT DISTINCT `%sourceType` FROM `%pivotTable`;';
        $rows = $db->query(strtr($sql, $binds), PDO::FETCH_ASSOC);
        if ($rows->rowCount()) {
            error_log(get_called_class() . '::' . __FUNCTION__);

            /** @todo Confirm each distinct source type */

            foreach ($rows as $row) {
                try {
                    $model = $this->modelFactory()->get($row['object_type']);
                } catch (Exception $e) {
                    unset($e);
                    $model = $row['object_type'];
                }

                if ($model instanceof ModelInterface) {
                    $sql = 'SELECT p.* FROM `%pivotTable` AS p
                            WHERE p.`%sourceType` = :objType AND p.`%sourceId` NOT IN (
                                SELECT o.`%objectKey` FROM `%objectTable` AS o
                            );';
                    $binds = array_merge($defaultBinds, [
                        '%objectTable' => $model->source()->table(),
                        '%objectKey'   => $model->key(),
                    ]);
                    $rows = $source->dbQuery(
                        strtr($sql, $binds),
                        [ 'objType' => $row['object_type'] ]
                    );

                    /** @todo Show found rows, confirm deletion of dead relationships */

                    /*
                    $sql = 'DELETE FROM `%pivotTable` AS p
                            WHERE p.`%sourceType` = :objType AND p.`%sourceId` NOT IN (
                                SELECT o.`%objectKey` FROM `%objectTable` AS o
                            );';
                    error_log('-- Delete Dead Objects: '.var_export($sql, true));
                    $source->dbQuery(
                        strtr($sql, $defaultBinds),
                        [ 'objType' => $model ]
                    );
                    */
                } elseif (is_string($model)) {
                    $sql  = 'SELECT p.* FROM `%pivotTable` AS p WHERE p.`%sourceType` = :objType;';
                    $rows = $source->dbQuery(
                        strtr($sql, $defaultBinds),
                        [ 'objType' => $model ]
                    );

                    /** @todo Explain missing model, confirm deletion of dead relationships */

                    /*
                    $sql = 'DELETE FROM `%pivotTable` WHERE `%sourceType` = :objType;';
                    error_log('-- Delete Dead Model: '.var_export($sql, true));
                    $source->dbQuery(
                        strtr($sql, $defaultBinds),
                        [ 'objType' => $model ]
                    );
                    */
                }
            }

            /*
            if (!$this->describeCount(
                $this->total,
                '%d dead objects were found.',
                '%d dead object was found.',
                'All objects are associated!'
            )) {
                return $this;
            }
            */

            $this->conclude();
        }

        return $this;
    }

    /**
     * Prune orphan attachments.
     *
     * @return self
     */
    protected function pruneAttachments()
    {
        $cli   = $this->climate();
        $ask   = $this->interactive();
        $dry   = $this->dryRun();
        $verb  = $this->verbose();
        $mucho = ($dry || $verb);

        $attach = $this->modelFactory()->get(Attachment::class);
        $pivot  = $this->modelFactory()->get(Join::class);
        $loader = $this->collection(Attachment::class);

        $sql = 'SELECT a.* FROM `%attachTable` AS a
                LEFT JOIN `%pivotTable` AS p
                ON a.id = p.attachment_id
                WHERE p.attachment_id IS NULL;';
        $binds = [
            '%attachTable' => $attach->source()->table(),
            '%pivotTable'  => $pivot->source()->table(),
        ];

        $collection = $loader->loadFromQuery(strtr($sql, $binds));

        $this->total = count($collection);
        if (
            !$this->describeCount(
                $this->total,
                '%d unused attachments were found.',
                '%d unused attachment was found.',
                'All attachments are associated!'
            )
        ) {
            return $this;
        }

        $prompt = $this->promptToStart();
        if ($prompt === 'view') {
            $this->setVerbose(true);
            $this->setQuiet(false);
            $mucho = true;
            $dry = true;
            $ask = false;
        } elseif ($prompt === false) {
            $cli->br()->info('Canceled Cleanup');
            return $this;
        }

        if ($ask) {
            $cli->br();
        }

        $data   = [];
        $length = strlen(strval($this->total));

        $this->remainder = $this->total;
        $this->indent    = str_repeat(' ', (($length * 2) + 4));

        $prop = $attach->property($attach->key());
        if ($prop && preg_match('~\b\w+\((?<length>\d+)\)~', $prop->sqlType(), $matches)) {
            $pad = (intval($matches['length']) + 5);
        } else {
            $pad = 20;
        }

        foreach ($collection as $obj) {
            $this->remainder--;
            $this->processed++;

            $title = strval($obj->title());
            $objId = $obj->id();

            if ($mucho) {
                $dead = [
                    'Title'       => $title,
                    'Created on'  => $obj->created()->format('Y-m-d H:i:s'),
                    'Created by'  => $obj->createdBy(),
                    'Modified on' => $obj->lastModified()->format('Y-m-d H:i:s'),
                    'Modified by' => $obj->lastModifiedBy(),
                    'Type'        => $obj->type(),
                ];

                if ($ask && $verb) {
                    $dead = array_merge([ 'Deleted' => null, 'Failed' => null ], $dead);
                }
            }

            if ($ask) {
                $type  = sprintf('[%s]', $obj->microType());
                $label = sprintf('#%1$s %2$s', str_pad($objId, $pad), str_pad($type, 20));
                if ($title) {
                    $label = sprintf('%1$s "%2$s"', $label, $title);
                }

                $cli->out(
                    sprintf(
                        '%' . $length . 'd/%-' . $length . 's — <yellow>%s</yellow>',
                        $this->processed,
                        $this->total,
                        $label
                    )
                );
            }

            if ($dry) {
                $this->pruned++;

                if ($mucho) {
                    $data[$objId] = $dead;
                }
            } elseif ($ask) {
                if ($this->action === null || $this->repeat === 0) {
                    $this->promptForDeletion();
                }

                if ($this->action === 'y') {
                    $this->deleteObject($obj, $this->pruned, $this->failed, $dead);
                } else {
                    $this->ignored++;
                }

                if ($this->repeat > 0) {
                    $this->repeat--;
                }

                if ($mucho) {
                    $data[$objId] = $dead;
                }

                continue;
            }
        }

        if ($ask) {
            $input = $cli->confirm('Reset auto-incremented IDs?');
            $cli->br();
            if ($input->confirmed()) {
                $sql = 'SET @num := 0;
                        UPDATE `%pivotTable` SET id = @num := (@num + 1);
                        ALTER TABLE `%pivotTable` AUTO_INCREMENT = 1;';
                $attach->source()->db()->query(strtr($sql, $binds));
            }
        }

        if ($mucho && $data) {
            $input = $cli->confirm('View details of cleanup?');
            if (!$ask) {
                $cli->br()->table($data);
            } else {
                $cli->br();
                if ($input->confirmed()) {
                    $cli->br()->table($data);
                }
            }
        }

        $cli->br();

        if ($dry) {
            if ($this->pruned) {
                $this->messages[] = sprintf(
                    '<yellow>%d attachment%s can be removed.</yellow>',
                    $this->pruned,
                    ($this->pruned > 1 ? 's' : '')
                );
            }
        } else {
            if ($this->pruned) {
                $this->messages[] = sprintf(
                    '<green>%d attachment%s successfully removed.</green>',
                    $this->pruned,
                    ($this->pruned > 1 ? 's were' : ' was')
                );
            }

            if ($this->failed) {
                $this->messages[] = sprintf(
                    '<light_red>%d attachment%s could not be removed.</light_red>',
                    $this->failed,
                    ($this->failed > 1 ? 's' : '')
                );
            }

            if ($this->ignored) {
                $this->messages[] = sprintf(
                    '%d unused attachment%s were ignored.',
                    $this->ignored,
                    ($this->ignored > 1 ? 's' : '')
                );
            }
        }

        $this->conclude();

        return $this;
    }

    /**
     * Display stored messages or a generic conclusion.
     *
     * @return self
     */
    protected function conclude()
    {
        $cli = $this->climate();

        if (count($this->messages)) {
            $cli->out(implode(' ', $this->messages));
            $this->messages = [];
        } else {
            $cli->info('Done!');
        }

        return $this;
    }

    /**
     * Describe the given object count.
     *
     * @param  integer $count    The object count.
     * @param  string  $plural   The message when the count is more than 1.
     * @param  string  $singular The message when the count is 1.
     * @param  string  $zero     The message when the count is zero.
     * @throws InvalidArgumentException If the given argument is not an integer.
     * @return boolean
     */
    protected function describeCount($count, $plural, $singular, $zero)
    {
        if (!is_int($count)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Must be an integer',
                    is_object($count) ? get_class($count) : gettype($count)
                )
            );
        }

        $cli = $this->climate();

        if ($count === 0) {
            $cli->info(
                sprintf($zero, $count)
            );
            return false;
        } elseif ($count === 1) {
            $cli->comment(
                sprintf($singular, $count)
            );
        } else {
            $cli->comment(
                sprintf($plural, $count)
            );
        }

        $cli->br();

        return true;
    }

    /**
     * Delete the given object.
     *
     * @param  AttachableInterface $obj      The object to delete.
     * @param  integer|null        $pruned   Count the number of deleted objects.
     * @param  integer|null        $failed   Count the number of failed deletions.
     * @param  array|null          $feedback Update the feedback.
     * @return boolean
     */
    protected function deleteObject(AttachableInterface $obj, &$pruned = null, &$failed = null, array &$feedback = null)
    {
        $verb = $this->verbose();

        if ($obj->delete()) {
            if ($feedback && $verb) {
                $feedback['Deleted'] = str_pad('✓', 7, ' ', STR_PAD_BOTH);
            }

            if (is_int($pruned)) {
                $pruned++;
            }

            return true;
        } else {
            if ($feedback && $verb) {
                $feedback['Failed'] = str_pad('×', 7, ' ', STR_PAD_BOTH);
            }

            if (is_int($failed)) {
                $failed++;
            }

            return false;
        }
    }

    /**
     * Prompt the user to confirm an action (deletion).
     *
     * @uses   \League\CLImate\TerminalObject\Dynamic\Input
     * @return boolean|void
     */
    public function promptForDeletion()
    {
        $cli = $this->climate();

        if (is_string($this->action)) {
            $opts = [ 'y', 'n', 'r', 'q', '?' ];
            $help = [
                '[y]es    — delete the attachment',
                '[n]o     — don’t delete the attachment',
                '[r]epeat — apply previous choice to remaining attachments',
                '[q]uit   — exit immediately (does not undo previous actions)',
            ];
        } else {
            $opts = [ 'y', 'a', 'n', 's', 'q', '?' ];
            $help = [
                '[y]es  — delete this attachment',
                '[a]ll  — delete all attachments',
                '[n]o   — don’t delete this attachment',
                '[s]kip — don’t delete attachments',
                '[q]uit — exit immediately (does not undo previous actions)',
            ];
        }

        $input = $cli->input($this->indent . 'Delete attachment');
        $input->accept($opts, true);

        $value = $input->prompt();
        switch ($value) {
            case '?':
                $cli->br();
                $cli->out($this->indent . implode("\n" . $this->indent, $help));
                $cli->br();
                return $this->promptForDeletion();

            case 'q':
                exit;

            case 'r':
                $this->repeat = -1;
                return ($this->action === 'y');

            case 's':
                $this->repeat = -1;
                $this->action = 'n';
                return false;

            case 'a':
                $this->repeat = -1;
                $this->action = 'y';
                return true;

            case 'n':
                $this->repeat = 0;
                $this->action = 'n';
                return false;

            case 'y':
            default:
                $this->repeat = 0;
                $this->action = 'y';
                return true;
        }
    }

    /**
     * Prompt the user to confirm an action (cotinue with cleanup).
     *
     * @uses   \League\CLImate\TerminalObject\Dynamic\Input
     * @return boolean|void
     */
    public function promptToStart()
    {
        $cli = $this->climate();

        $opts = [ 'y', 'n', 'v', '?' ];
        $help = [
            '[y]es  — continue to deletion phase',
            '[n]o   — cancel deletion phase',
            '[v]iew — view unused attachments',
        ];

        $input = $cli->input($this->indent . 'Continue');
        $input->accept($opts, true);

        $value = $input->prompt();
        switch ($value) {
            case '?':
                $cli->br();
                $cli->out($this->indent . implode("\n" . $this->indent, $help));
                $cli->br();
                return $this->promptToStart();

            case 'v':
                return 'view';

            case 'n':
                return false;

            case 'y':
            default:
                return true;
        }
    }
}
