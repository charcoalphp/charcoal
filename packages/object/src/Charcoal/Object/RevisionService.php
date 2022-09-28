<?php

namespace Charcoal\Object;

use Charcoal\Model\ModelFactoryTrait;
use Charcoal\Model\ModelInterface;

/**
 * Revision Service
 *
 * Service handling revision generation and retrieval.
 * Can be implemented in a listener
 */
class RevisionService
{
    use ModelFactoryTrait;

    private RevisionConfig $revisionConfig;
    private array $modelRevisionConfig;

    public function __construct(array $dependencies)
    {
        $this->revisionConfig = $dependencies['revision/config'];
        $this->setModelFactory($dependencies['model/factory']);
    }

    public function generateRevision(ModelInterface $model): ?ObjectRevisionInterface
    {
        // Bail early
        if (
            !$this->revisionConfig->isEnabled() ||
            !$this->canCreateRevision($model)
        ) {
            return null;
        }

        $modelConfig = $this->getModelRevisionConfig($model);
        $revisionProperties = $this->parseRevisionProperties($model);

        $revisionObject = $this->createRevisionObject($modelConfig->getRevisionClass());
        $revisionObject->createFromObject($model, $revisionProperties);

        if (!empty($revisionObject->getDataDiff())) {
            $revisionObject->save();
        }

        return $revisionObject;
    }

    public function parseRevisionProperties(ModelInterface $model): array
    {
        $modelConfig = $this->getModelRevisionConfig($model);
        $properties = array_keys($model->data());

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

    public function createRevisionObject(string $objectRevisionClass = ObjectRevision::class): ObjectRevisionInterface
    {
        return $this->modelFactory()->create($objectRevisionClass);
    }

    public function canCreateRevision(ModelInterface $model): bool
    {
        $revisionConfig = $this->getModelRevisionConfig($model);

        // If we did not find a config of the value of the config is false, we don't want to revision.
        if (!$revisionConfig) {
            return false;
        }

        return $revisionConfig->isEnabled();
    }

    private function getModelRevisionConfig(ModelInterface $model): ?RevisionModelConfig
    {
        if (!isset($this->modelRevisionConfig[get_class($model)])) {
            $this->modelRevisionConfig[get_class($model)] = $this->revisionConfig->buildModelConfig($model);
        }

        return $this->modelRevisionConfig[get_class($model)];
    }
}
