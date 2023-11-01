<?php

namespace Charcoal\Attachment\Script;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
// From 'charcoal-property'
use Charcoal\Property\IdProperty;
use Charcoal\Property\PropertyField;
// From 'charcoal-admin'
use Charcoal\Admin\Script\Object\Table\AlterPrimaryKeyScript;
// From 'charcoal-attachment'
use Charcoal\Attachment\Object\Attachment;
use Charcoal\Attachment\Object\Join;

/**
 * Alter the primary keys of attachments.
 *
 * Tailored for Attachments—used without options—to upgrade 0.1 packages to 0.2+.
 */
class MigrateScript extends AlterPrimaryKeyScript
{
    /**
     * The related model that handles associations.
     *
     * @var ModelInterface|null
     */
    protected $pivotModel;

    /**
     * @return void
     */
    protected function init()
    {
        $this->setArguments($this->defaultArguments());

        $this->setDescription(
            'The <underline>attachment/migrate</underline> script ' .
            'upgrades the attachments SQL tables from 0.1 to 0.2; ' .
            'Auto-incremented IDs are replaced with generated unique IDs.'
        );
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
        $cli->bold()->underline()->out('Migrate Attachments from 0.1 to 0.2');
        $cli->br();

        $pivot  = $this->pivotModel();
        $attach = $this->targetModel();

        $pivotSource  = $pivot->source();
        $pivotTable   = $pivotSource->table();

        $attachSource = $attach->source();
        $attachTable  = $attachSource->table();

        $targets = [
            $attachTable => $attachSource,
            $pivotTable  => $pivotSource,
        ];

        $cli->out('The following tables will be altered:');
        foreach ($targets as $table => $source) {
            $cli->comment(sprintf('  • %s', $table));
        }
        $cli->br();
        $cli->shout('This process is destructive. A backup should be made before proceeding.');
        $cli->br();
        $cli->red()->flank(
            sprintf(
                'Attachments associated outside of the [%s] table are not affected.',
                $pivotTable
            ),
            '!'
        );
        $cli->br();

        $input = $cli->confirm('Continue?');
        if ($input->confirmed()) {
            $cli->info('Starting Conveversion');
        } else {
            $cli->info('Canceled Conveversion');
            return $this;
        }

        $cli->br();

        $db = $attachSource->db();
        if (!$db) {
            $cli->error(
                'Could not instanciate a database connection.'
            );
            return $this;
        }

        foreach ($targets as $table => $source) {
            if (!$source->tableExists()) {
                $cli->error(
                    sprintf('The table "%s" does not exist.', $table) .
                    ' This script can only alter existing tables.'
                );
                return $this;
            }
        }

        $oldKey = $attach->key();
        $newKey = sprintf('%s_new', $attach->key());

        $db->query(
            strtr(
                'LOCK TABLES
                    `%attachTable` WRITE,
                    `%attachTable` AS a WRITE,
                    `%pivotTable` AS p WRITE;',
                [
                    '%attachTable' => $attachTable,
                    '%pivotTable'  => $pivotTable,
                ]
            )
        );

        $this->prepareProperties($oldKey, $newKey, $oldProp, $newProp);

        if ($newProp->mode() === $oldProp->mode()) {
            $cli->error(
                sprintf(
                    'The ID is already %s. Canceling conversion.',
                    $this->labelFromMode($newProp)
                )
            );
            $db->query('UNLOCK TABLES;');
            return $this;
        }

        $newField = $this->propertyField($newProp);
        $oldField = $this->propertyField($oldProp);
        $oldField->setExtra('');

        if (!$this->quiet()) {
            $this->describeConversion($newProp);
        }

        $this->convertIdField($newProp, $newField, $oldProp, $oldField);

        $db->query('UNLOCK TABLES;');

        if (!$this->quiet()) {
            $cli->br();
            $cli->info('Success!');
        }

        return $this;
    }



    // Alter Table
    // =========================================================================

    /**
     * Sync the new primary keys to pivot table.
     *
     * @param  IdProperty    $newProp  The new ID property.
     * @param  PropertyField $newField The new ID field.
     * @param  IdProperty    $oldProp  The previous ID property.
     * @param  PropertyField $oldField The previous ID field.
     * @throws InvalidArgumentException If the new property does not implement the proper mode.
     * @return self
     */
    protected function syncRelatedFields(
        IdProperty $newProp,
        PropertyField $newField,
        IdProperty $oldProp,
        PropertyField $oldField
    ) {
        unset($newProp, $oldProp, $oldField);

        $cli = $this->climate();
        if (!$this->quiet()) {
            $cli->br();
            $cli->comment('Syncing new IDs to pivot table.');
        }

        $pivot  = $this->pivotModel();
        $attach = $this->targetModel();

        $pivotSource  = $pivot->source();
        $pivotTable   = $pivotSource->table();

        $attachSource = $attach->source();
        $attachTable  = $attachSource->table();

        $db = $attachSource->db();

        $binds = [
            // Join Model
            '%pivotTable'   => $pivotTable,
            '%objectType'   => 'object_type',
            '%objectId'     => 'object_id',
            '%targetId'     => 'attachment_id',

            // Attachment Model
            '%targetTable'  => $attachTable,
            '%targetType'   => 'type',
            '%newKey'       => $newField->ident(),
            '%oldKey'       => $attach->key(),
        ];

        // Update simple object → attachment associations
        $sql = 'UPDATE `%pivotTable` AS p
                JOIN `%targetTable` AS a
                ON p.`%targetId` = a.`%oldKey`
                SET p.`%targetId` = a.`%newKey`;';
        $db->query(strtr($sql, $binds));

        // Update nested attachment → attachment associations
        $sql = 'UPDATE `%pivotTable` AS p
                JOIN `%targetTable` AS a
                ON p.`%objectId` = a.`%oldKey` AND p.`%objectType` = a.`%targetType`
                SET p.`%objectId` = a.`%newKey`;';
        $db->query(strtr($sql, $binds));

        return $this;
    }



    // CLI Arguments
    // =========================================================================

    /**
     * Retrieve the script's supported arguments.
     *
     * @return array
     */
    public function defaultArguments()
    {
        static $arguments;

        if ($arguments === null) {
            $arguments = [
                'keep_id' => [
                    'longPrefix'  => 'keep-id',
                    'noValue'     => true,
                    'description' => 'Skip the deletion of the ID field to be replaced.',
                ]
            ];

            $arguments = array_merge($this->parentArguments(), $arguments);
        }

        return $arguments;
    }

    /**
     * Retrieve the model to alter.
     *
     * @return ModelInterface
     */
    public function targetModel()
    {
        if (!isset($this->targetModel)) {
            $this->targetModel = $this->modelFactory()->get(Attachment::class);
        }

        return $this->targetModel;
    }

    /**
     * Retrieve the attachment association model.
     *
     * @return ModelInterface
     */
    public function pivotModel()
    {
        if (!isset($this->pivotModel)) {
            $this->pivotModel = $this->modelFactory()->get(Join::class);
        }

        return $this->pivotModel;
    }
}
