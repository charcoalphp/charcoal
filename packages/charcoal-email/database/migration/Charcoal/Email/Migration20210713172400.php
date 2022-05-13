<?php

namespace Charcoal\Email;

use Charcoal\DatabaseMigrator\AbstractMigration;
use Charcoal\Model\ModelFactoryTrait;
use PDOException;
use Pimple\Container;

/**
 * Migration 2021-07-13 17:24:00
 */
final class Migration20210713172400 extends AbstractMigration
{
    use ModelFactoryTrait;

    /**
     * Short description of what the patch will do.
     *
     * @return string
     */
    public function description(): string
    {
        return 'Email Logs // Alter EmailLog table to match changes made in 7c230fb';
    }

    /**
     * Inject dependencies from a DI Container.
     *
     * @param Container $container A Pimple DI service container.
     * @return void
     */
    protected function setDependencies(Container $container): void
    {
        parent::setDependencies($container);

        $this->setModelFactory($container['model/factory']);
    }

    /**
     * Apply migration
     *
     * @return void
     */
    public function up(): void
    {
        try {
            /** @var EmailQueueItem $proto */
            $proto = $this->modelFactory()->create(EmailLog::class);
            $table = $proto->source()->table();
            $proto->source()->alterTable();

            $this->addFeedback(
                "Updating <white>${table} Table</white>."
            );

            $this->createOrAlter($proto);
        } catch (PDOException $exception) {
            $this->addError($exception->getMessage());
            $this->setStatus(self::FAILED_STATUS);
        }

        $this->setStatus(self::PROCESSED_STATUS);
    }
}
