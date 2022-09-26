<?php

namespace Charcoal\Object;

use Charcoal\Admin\Config;
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

    private Config $config;

    public function __construct(array $dependencies)
    {
        $this->config = $dependencies['config'];
        $this->setModelFactory($dependencies['model/factory']);
    }

    public function generateRevision(ModelInterface $model): ?ObjectRevisionInterface
    {
        if (!$this->canCreateRevision($model)) {
            return null;
        }

        $config = $this->findRevisionsConfig($model);

        $revisionObject = $this->createRevisionObject($config->get('revisionClass'));
        $revisionObject->createFromObject($model);

        if (!empty($revisionObject->getDataDiff())) {
            $revisionObject->save();
        }

        return $revisionObject;
    }

    public function createRevisionObject(string $objectRevisionClass = ObjectRevision::class): ObjectRevisionInterface
    {
        return $this->modelFactory()->create($objectRevisionClass);
    }

    public function canCreateRevision(ModelInterface $model): bool
    {
        if (!$this->config->get('revision_enabled')) {
            return false;
        }

        $revisionConfig = $this->findRevisionsConfig($model);

        // If we did not find a config of the value of the config is false, we don't want to revision.
        if (!$revisionConfig) {
            return false;
        }

        return ($revisionConfig['enabled'] ?? true);
    }

    public function findRevisionsConfig(ModelInterface $model): ?RevisionConfig
    {
        $class = get_class($model);
        if (in_array($class, $this->getRevisionableClasses())) {
            return $this->getRevisionsConfig($class);
        }

        // Allows ancestor level configuration.
        foreach ($this->getRevisionableClasses() as $class) {
            if ($model instanceof $class) {
                return $this->getRevisionsConfig($class);
            }
        }

        return null;
    }

    private function getRevisionsConfig(string $class): RevisionConfig
    {
        $revisions = $this->config->get('revisions');
        $revisionsData = $revisions[$class];

        // If a config is a boolean instead of a data array, it means we only want to affect the enabled state.
        if (is_bool($revisionsData)) {
            $revisionsData = [
                'enabled' => $revisionsData
            ];
        }

        return new RevisionConfig($revisionsData);
    }

    public function getRevisionableClasses(): array
    {
        return array_keys($this->config->get('revisions'));
    }
}
