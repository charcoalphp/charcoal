<?php

namespace Charcoal\Email;

use Charcoal\DatabaseMigrator\AbstractMigration;
use Charcoal\Model\ModelFactoryTrait;
use PDOException;
use Pimple\Container;

/**
 * Migration 2020-08-27 13:19:00
 */
class Migration20200827131900 extends AbstractMigration
{
    use ModelFactoryTrait;

    /**
     * @return string
     */
    public function description(): string
    {
        return 'Email queue item // add expiry date';
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
            $proto = $this->modelFactory()->create(EmailQueueItem::class);
            $table = $proto->source()->table();

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
