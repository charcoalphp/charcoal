<?php

namespace Charcoal\Object;

use Charcoal\Config\AbstractConfig;
use Charcoal\Model\ModelInterface;

/**
 * Revision Config
 *
 * Configuration for the project's revision system
 */
class RevisionConfig extends AbstractConfig
{
    protected bool $enabled = true;
    protected string $revisionClass = ObjectRevision::class;
    // TODO implement the limit feature.
    protected ?int $limitPerModel = null;
    protected array $models = [];
    // Exclude properties from the revision process.
    protected array $excludedProperties = [];

    /**
     * @param ModelInterface $model
     * @return ?RevisionModelConfig
     */
    public function buildModelConfig(ModelInterface $model): ?RevisionModelConfig
    {
        $class = get_class($model);

        if (isset($models[$class])) {
            return $this->prepareRevisionModelConfig($model, $models[$class]);
        }

        // If the exact class is not defined in the revisions models key, try to find options from inheritance.
        foreach ($this->models as $class => $revisionOptions) {
            if ($model instanceof $class) {
                return $this->prepareRevisionModelConfig($model, $revisionOptions);
            }
        }

        return null;
    }

    /**
     * @param ModelInterface $model
     * @param array|boolean  $revisionOptions
     * @return RevisionModelConfig
     */
    private function prepareRevisionModelConfig(ModelInterface $model, $revisionOptions): RevisionModelConfig
    {
        // If a config is a boolean instead of a data array, it means we only want to affect the enabled state.
        if (is_bool($revisionOptions)) {
            $revisionOptions = [
                'enabled' => $revisionOptions,
            ];
        }

        $extraOptions = [
            'excludedProperties' => $this->getExcludedProperties(),
        ];

        // Extract excludedProperties options from the model's ancestors.
        foreach ($this->models as $class => $modelConfig) {
            if ($model instanceof $class) {
                // keep only excludedProperties from ancestors
                $modelConfig  = array_intersect_key($modelConfig, array_flip(['excludedProperties']));
                $extraOptions = array_merge_recursive($extraOptions, $modelConfig);
            }
        }

        return new RevisionModelConfig(array_merge($revisionOptions, $extraOptions));
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getRevisionClass(): string
    {
        return $this->revisionClass;
    }

    /**
     * @param string $revisionClass RevisionClass for RevisionConfig.
     * @return self
     */
    public function setRevisionClass(string $revisionClass): self
    {
        $this->revisionClass = $revisionClass;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimitPerModel(): ?int
    {
        return $this->limitPerModel;
    }

    /**
     * @param int|null $limitPerModel LimitPerModel for RevisionConfig.
     * @return self
     */
    public function setLimitPerModel(?int $limitPerModel): self
    {
        $this->limitPerModel = $limitPerModel;

        return $this;
    }

    /**
     * @return array
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * @param array $models Models for RevisionConfig.
     * @return self
     */
    public function setModels(array $models): self
    {
        $this->models = $models;

        return $this;
    }

    /**
     * @return array
     */
    public function getExcludedProperties(): array
    {
        return $this->excludedProperties;
    }

    /**
     * @param array $excludedProperties ExcludedProperties for RevisionConfig.
     * @return self
     */
    public function setExcludedProperties(array $excludedProperties): self
    {
        $this->excludedProperties = $excludedProperties;

        return $this;
    }
}
