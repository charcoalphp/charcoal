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

        // TODO config should be a config class.
        $config = $this->findRevisionsConfig($model);

        $revisionObject = isset($config['revision_class'])
            ? $this->createRevisionObject($config['revision_class'])
            : $this->createRevisionObject();
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

        return ($revisionConfig['enabled'] ?? true);
    }

    public function findRevisionsConfig(ModelInterface $model): array
    {
        $class = get_class($model);
        if (in_array($class, $this->getRevisionableClasses())) {
            return $this->getRevisionsConfig($class);
        }

        foreach ($this->getRevisionableClasses() as $revisonable) {
            if ($model instanceof $revisonable) {
                $this->getRevisionsConfig($revisonable);
            }
        }
    }

    public function getRevisionsConfig(?string $class = null): array
    {
        $revisions = $this->config->get('revisions');

        return $class ? $revisions[$class] : $revisions;
    }

    public function getRevisionableClasses(): array
    {
        return array_keys($this->getRevisionsConfig());
    }
}
