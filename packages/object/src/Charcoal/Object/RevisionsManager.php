<?php

namespace Charcoal\Object;

use Charcoal\Loader\CollectionLoader;
use Charcoal\Model\ModelFactoryTrait;
use Charcoal\Model\ModelInterface;
use Pimple\Psr11\ServiceLocator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Revisions Manager
 *
 * Service handling revision generation and retrieval.
 * Can be implemented in an event listener
 *
 * Revisions need to act on a ModelInterface,
 * So to use the revision service, one have to set a ModelInterface beforehand.
 * Failure to do so will result in an \InvalidArgumentException being thrown.
 */
class RevisionsManager
{
    use ModelFactoryTrait;
    use LoggerAwareTrait;

    private RevisionsConfig $revisionConfig;
    private array $modelRevisionConfig;
    private ModelInterface $model;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ServiceLocator $locator)
    {
        $this->revisionConfig = $locator->get('revisions/config');
        $this->setModelFactory($locator->get('model/factory'));
        $this->setLogger($locator->get('logger'));
    }

    public function __invoke(ModelInterface $model): self
    {
        $this->setModel($model);

        return $this;
    }

    public function generateRevision(): ?ObjectRevisionInterface
    {
        $model = $this->getModel();

        // Bail early
        if (
            !$this->revisionConfig->isEnabled() ||
            !$this->isRevisionEnabled()
        ) {
            return null;
        }

        $revisionProperties = $this->parseRevisionProperties();
        $revisionObject     = $this->createRevisionObject();

        $revisionObject->createFromObject($model, $revisionProperties);

        if (!empty($revisionObject->getDataDiff())) {
            $revisionObject->save();
        }

        return $revisionObject;
    }

    public function getLatestRevision(): ObjectRevisionInterface
    {
        $model    = $this->getModel();
        $revision = $this->createRevisionObject();

        return $revision->lastObjectRevision($model);
    }

    /**
     * @return ObjectRevisionInterface[]
     */
    public function getAllRevisions(callable $callback = null): array
    {
        $model  = $this->getModel();
        $loader = $this->createRevisionObjectCollectionLoader();

        $loader
            ->addOrder('revTs', 'desc')
            ->addFilters([
                [
                    'property' => 'targetType',
                    'value'    => $model->objType(),
                ],
                [
                    'property' => 'targetId',
                    'value'    => $model->id(),
                ],
            ]);

        if ($callback !== null) {
            $loader->setCallback($callback);
        }

        $revisions = $loader->load();

        return $revisions->objects();
    }

    public function revertToRevision(int $number): bool
    {
        $model    = $this->getModel();
        $revision = $this->getRevisionFromNumber($number);

        if (!$revision->id()) {
            return false;
        }

        if (isset($model['lastModifiedBy'])) {
            $model['lastModifiedBy'] = $revision->getRevUser();
        }

        $model->setData($revision->getDataObj());

        return $model->update();
    }

    public function parseRevisionProperties(): array
    {
        $model       = $this->getModel();
        $modelConfig = $this->getModelRevisionConfig($model);
        $properties  = array_keys($model->data());

        if ($modelConfig->hasProperties()) {
            return array_intersect($properties, $modelConfig->getProperties());
        }

        if ($modelConfig->hasExcludedProperties()) {
            $excludedProperties = $modelConfig->getExcludedProperties();

            if ($modelConfig->hasIncludedProperties()) {
                $includedProperties = $modelConfig->getIncludedProperties();
                $excludedProperties = array_filter($excludedProperties, fn($e) => !in_array($e, $includedProperties));
            }

            return array_filter(
                $properties,
                fn($n) => !in_array($n, $excludedProperties)
            );
        }

        return $properties;
    }

    public function getObjectRevisionClass(): string
    {
        $modelConfig = $this->getModelRevisionConfig();

        return $modelConfig->getRevisionClass();
    }

    public function createRevisionObjectCollectionLoader(): CollectionLoader
    {
        return new CollectionLoader([
            'logger'  => $this->logger,
            'factory' => $this->modelFactory(),
            'model'   => $this->getRevisionObjectPrototype($this->getObjectRevisionClass()),
        ]);
    }

    public function getRevisionObjectPrototype(): ObjectRevisionInterface
    {
        return $this->modelFactory()->get($this->getObjectRevisionClass());
    }

    public function createRevisionObject(): ObjectRevisionInterface
    {
        return $this->modelFactory()->create($this->getObjectRevisionClass());
    }

    public function getRevisionFromNumber(int $number): ObjectRevisionInterface
    {
        return $this->createRevisionObject()->objectRevisionNum($this->getModel(), $number);
    }

    public function isRevisionEnabled(): bool
    {
        $model          = $this->getModel();
        $revisionConfig = $this->getModelRevisionConfig($model);

        // If we did not find a config of the value of the config is false, we don't want to revision.
        if (!$revisionConfig) {
            return false;
        }

        return $revisionConfig->isEnabled();
    }

    private function getModelRevisionConfig(): ?RevisionModelConfig
    {
        $model = $this->getModel();

        if (!isset($this->modelRevisionConfig[get_class($model)])) {
            $this->modelRevisionConfig[get_class($model)] = $this->revisionConfig->buildModelConfig($model);
        }

        return $this->modelRevisionConfig[get_class($model)];
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function setModel(ModelInterface $model): self
    {
        $this->model = $model;

        return $this;
    }
}
